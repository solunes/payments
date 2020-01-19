<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Payments {

    public static function generateSalePayment($sale, $model_name, $redirect, $type) {
        $payment = \Payments::generatePayment($sale);
        $cancel_url = url('payments/finish-payment/'.$payment->id);

        $model = new $model_name;
        if($model_name=='\OmnipayGateway'){
            $api_url = $model->generateSalePayment($payment, $cancel_url, $type);
        } else {
            $api_url = $model->generateSalePayment($payment, $cancel_url);
        }
        if($api_url){
            return redirect($api_url);
        } else {
            return redirect($redirect)->with('message_error', 'Hubo un error al realizar el pago de la compra pendiente.');
        }
    }

    public static function getSalePaymentBridge($sale) {
        $item['id'] = $sale->id;
        $item['name'] = $sale->name;
        $subitems_array = [];
        $sale->load('sale_items');
        foreach($sale->sale_items as $payment_item){
            $subitems_array[] = \Payments::generatePaymentItem($payment_item->name, $payment_item->quantity, $payment_item->amount, 0);
        }
        $item['amount'] = $sale->amount;
        $item['items'] = $subitems_array;
        return $item;
    }

    public static function getSaleCustomerBridge($sale) {
        $item['id'] = $sale->customer_id;
        $item['email'] = $sale->user->email;
        $item['name'] = $sale->user->full_name;
        $item['nit_name'] = $sale->user->name;
        $item['nit_number'] = $sale->user->cellphone;
        $item['pending_payments'] = [];
        return $item;
    }

    public static function generateAppKey() {
        $token = \Payments::generateToken([8,4,4,4,12]);
        return $token;
    }

    public static function generatePayment($sale) {
        $currency = $sale->currency;
        if(!$currency){
            $currency = \Solunes\Business\App\Currency::find(2);
        }
        $sale->load('sale_payments');
        $sale->load('sale_items');
        $sale_payments_array = [];
        $payment = NULL;
        foreach($sale->sale_payments as $sale_payment){
            $sale_payment->load('sale_payment_items');
            if(!$payment = $sale_payment->payment){
                $payment = new \Solunes\Payments\App\Payment;
                $payment->customer_id = $sale->customer_id;
                $payment->currency_id = $currency->id;
            }
            $payment->name = $sale->name;
            $payment->customer_name = $sale->customer->name;
            $payment->customer_email = $sale->customer->email;
            $payment->date = $sale->created_at;
            $payment->invoice = $sale->invoice;
            $payment->invoice_name = $sale->invoice_name;
            $payment->invoice_nit = $sale->invoice_nit;
            $payment->real_amount = \Business::calculate_currency($sale_payment->amount, $currency, $sale_payment->currency);
            if(config('payments.sfv_version')>1||config('payments.discounts')){
                $payment->discount_amount = \Business::calculate_currency($sale_payment->discount_amount, $currency, $sale_payment->currency);
            }
            $payment->status = 'holding';
            if(config('payments.sfv_version')>1){
                $payment->commerce_user_code = $sale_payment->commerce_user_code;
                $payment->customer_code = $sale_payment->customer_code;
                $payment->customer_ci_number = $sale_payment->customer_ci_number;
                $payment->customer_ci_extension = $sale_payment->customer_ci_extension;
                $payment->customer_ci_expedition = $sale_payment->customer_ci_expedition;
                $payment->invoice_type = $sale_payment->invoice_type;
                $payment->payment_type_code = $sale_payment->payment_type_code;
                $payment->card_number = $sale_payment->card_number;
            }
            if(config('payments.sfv_version')>1||config('payments.discounts')){
                $payment->discount_amount = $sale_payment->discount_amount;
            }
            $payment->save();

            $subitem_total = 0;
            $salepayment_total = $sale_payment->amount;
            foreach($sale->sale_items as $sale_item){
                if($salepayment_total>0&&(!isset($sale_payments_array[$sale_item->id])||$sale_payments_array[$sale_item->id]>0)){
                    if(!$sale_payment_item = \Solunes\Sales\App\SalePaymentItem::where('parent_id', $sale_payment->id)->where('sale_item_id', $sale_item->id)->first()){
                        $sale_payment_item = new \Solunes\Sales\App\SalePaymentItem;
                        $sale_payment_item->parent_id = $sale_payment->id;
                        $sale_payment_item->currency_id = $sale_payment->currency_id;
                        $sale_payment_item->sale_item_id = $sale_item->id;
                    }
                    if(isset($sale_payments_array[$sale_item->id])&&$sale_payments_array[$sale_item->id]>0){
                        $amount = $sale_payments_array[$sale_item->id];
                    } else {
                        $amount = $sale_item->price * $sale_item->quantity;
                    }
                    $amount = \Business::calculate_currency($amount, $payment->currency, $sale_item->currency);
                    $subitem_total += $amount;
                    $salepayment_total -= $amount;
                    $final_amount = $amount;
                    if($salepayment_total<0){
                        $final_amount += $salepayment_total;
                        $sale_payments_array[$sale_item->id] = $salepayment_total * (-1);
                        $sale_payment_item->pending_amount = $sale_payments_array[$sale_item->id];
                    } else {
                        $sale_payments_array[$sale_item->id] = 0;
                    }
                    $sale_payment_item->amount = $final_amount;
                    if(!$payment_item = \Solunes\Payments\App\PaymentItem::where('parent_id', $payment->id)->where('item_type', 'sale-item')->where('item_id', $sale_item->id)->first()){
                        $payment_item = new \Solunes\Payments\App\PaymentItem;
                        $payment_item->parent_id = $payment->id;
                        $payment_item->item_type = 'sale-item';
                        $payment_item->item_id = $sale_item->id;
                    }
                    if($sale_item->detail){
                        $payment_item->name = $sale_item->detail;
                    } else if($sale_item->product_bridge->name) {
                        $payment_item->name = $sale_item->product_bridge->name;
                    } else {
                        $payment_item->name = 'Detalle sin definir';
                    }
                    $payment_item->currency_id = $payment->currency_id;
                    $payment_item->quantity = $sale_item->quantity;
                    $payment_item->price = \Business::calculate_currency($sale_item->price, $payment->currency, $sale_item->currency);
                    $payment_item->amount = $final_amount;
                    if(config('payments.sfv_version')>1){
                        $payment_item->economic_sin_activity = $sale_item->economic_sin_activity;
                        $payment_item->product_sin_code = $sale_item->product_sin_code;
                        $payment_item->product_internal_code = $sale_item->product_internal_code;
                        $payment_item->product_serial_number = $sale_item->product_serial_number;
                    }
                    if(config('payments.sfv_version')>1||config('payments.discounts')){
                        $payment_item->discount_price = \Business::calculate_currency($sale_item->discount_price, $payment->currency, $sale_item->currency);
                        $payment_item->discount_amount = \Business::calculate_currency($sale_item->discount_amount, $payment->currency, $sale_item->currency);
                    }
                    $payment_item->save();
                    $sale_payment_item->save();
                }
            }
            if(config('sales.delivery')){
                if($sale_payment->pay_delivery){
                    $sale->load('sale_deliveries');
                    foreach($sale->sale_deliveries as $sale_delivery){
                        if($city = $sale_delivery->city){
                            $city_name = $city->name;
                        } else {
                            $city_name = $sale_delivery->city_other;
                        }
                        if($region = $sale_delivery->region){
                            $region_name = $region->name;
                        } else {
                            $region_name = $sale_delivery->region_other;
                        }
                        if(!$payment_shipping = \Solunes\Payments\App\PaymentShipping::where('parent_id', $payment->id)->first()){
                            $payment_shipping = new \Solunes\Payments\App\PaymentShipping;
                            $payment_shipping->parent_id = $payment->id;
                        }
                        $payment_shipping->name = $sale->name.' ('.$sale_delivery->total_weight.' Kg.)';
                        $payment_shipping->contact_name = $sale_delivery->name;
                        $payment_shipping->address = $sale_delivery->address;
                        $payment_shipping->address_2 = $sale_delivery->address_extra;
                        $payment_shipping->city = $city_name;
                        $payment_shipping->region = $region_name;
                        $payment_shipping->postal_code = $sale_delivery->postal_code;
                        $payment_shipping->country_code = $sale_delivery->country_code;
                        $payment_shipping->phone = $sale_delivery->phone;
                        $payment_shipping->price = $sale_delivery->shipping_cost;
                        $payment_shipping->save();
                    }
                }
            }
            if(!$sale_payment->payment_id){
                $sale_payment->payment_id = $payment->id;
                $sale_payment->save();
            }
        }
        return $payment;
    }
    
    public static function generatePaymentCode() {
        $token = \Payments::generateToken([8,4,4,4,12]);
        if(\Solunes\Payments\App\Transaction::where('payment_code', $token)->first()){
            $token = \Payments::generatePaymentCode();
        }
        return $token;
    }

    public static function generateToken($array) {
        $full_token = '';
        foreach($array as $key => $lenght){
            $token = bin2hex(openssl_random_pseudo_bytes($lenght/2));
            if($key!=0){
                $full_token .= '-';
            }
            $full_token .= $token;
        }
        return $full_token;
    }

    public static function encrypt($plainTextToEncrypt) {
        $secret_key = config('payments.salt');
        $secret_iv = config('payments.secret_iv');
          
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
     
        $output = base64_encode( openssl_encrypt( $plainTextToEncrypt, $encrypt_method, $key, 0, $iv ) );
        return $output;
    }
    
    public static function decrypt($textToDecrypt) {
        $secret_key = config('payments.salt');
        $secret_iv = config('payments.secret_iv');
     
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
     
        $output = openssl_decrypt( base64_decode( $textToDecrypt ), $encrypt_method, $key, 0, $iv );
        return $output;
    }

    public static function getShippingCost($payment, $payment_ids) {
        if(config('payments.shipping')) {
            $shipping_amount = 0;
            $payment_shippings = \Solunes\Payments\App\PaymentShipping::whereIn('parent_id', $payment_ids)->get();
            $address_detail = 'Costo de envío: ';
            foreach($payment_shippings as $payment_shipping){
                if($payment_shipping->city){
                    $address_detail .= $payment_shipping->city.' | ';
                }
                $address_detail .= $payment_shipping->address;
                $shipping_amount += $payment_shipping->price;
            }
            if(isset($payment['currency_exchange'])){
                $shipping_amount = $shipping_amount * $payment['currency_exchange'];
            }
            if(config('customer.enable_test')==1&&$shipping_amount>0){
                $payment['shipping_amount'] = 1;
            } else {
                $payment['shipping_amount'] = $shipping_amount;
            }
            $payment['shipping_detail'] = $address_detail;
        } else {
            $payment['shipping_amount'] = 0;
            $payment['shipping_detail'] = 'Sin costo de envío';
        }
        return $payment;
    }

    public static function generatePaymentCallback($payment_code, $external_payment_code = NULL) {
        $url = url('api/confirmed-payment/'.$payment_code);
        if($external_payment_code){
            $url .= '/'.$external_payment_code.'?external_payment_code='.$external_payment_code;
        }
        return $url;
    }

    public static function generatePaymentTransaction($customer_id, $payment_ids, $payment_method_code, $amount = NULL) {
        $payment_code = \Payments::generatePaymentCode();
        $payment_method = \Solunes\Payments\App\PaymentMethod::where('code', $payment_method_code)->first();
        if(!$payment_method||count($payment_ids)==0){
            return NULL;
        }
        $transaction = new \Solunes\Payments\App\Transaction;
        $transaction->customer_id = $customer_id;
        $transaction->payment_code = $payment_code;
        $transaction->payment_method_id = $payment_method->id;
        $transaction->save();
        foreach($payment_ids as $payment_id){
            $transaction_payment = new \Solunes\Payments\App\TransactionPayment;
            $transaction_payment->parent_id = $transaction->id;
            $transaction_payment->payment_id = $payment_id;
            $transaction_payment->save();
        }
        return $transaction;
    }

    public static function completePaymentTransaction($customer_id, $payment_code) {
        $transaction = \Solunes\Payments\App\Transaction::where('customer_id',$customer_id)->where('payment_code',$payment_code)->first();
        return $transaction;
    }

    public static function calculateMultiplePayments($payments_array, $amount = 0) {
        $total_amount = 0;
        $payment_ids = [];
        $items = [];
        foreach($payments_array as $payment_id => $pending_payment){
            $total_amount += $pending_payment['amount'];
            $payment_ids[] = $payment_id;
            foreach($pending_payment['items'] as $single_payment){
                $items[] = $single_payment;
            }
        }
        return ['items'=>$items, 'payment_ids'=>$payment_ids, 'total_amount'=>$amount];
    }

    public static function sendCustomerTo($url, $customer) {
        $url .= '/api/customer/new';
        
        $final_fields = [];
        $final_fields['app_key'] = config('payments.app_key');
        $final_fields['email'] = $customer->email;
        $final_fields['first_name'] = $customer->first_name;
        $final_fields['last_name'] = $customer->last_name;
        $final_fields['ci_number'] = $customer->ci_number;
        $final_fields['ci_expedition'] = $customer->ci_expedition;
        $final_fields['member_code'] = $customer->member_code;
        $final_fields['phone'] = $customer->phone;
        $final_fields['address'] = $customer->address;
        $final_fields['nit_number'] = $customer->nit_number;
        $final_fields['nit_name'] = $customer->nit_name;
        $final_fields['birth_date'] = $customer->birth_date;

        $ch = curl_init();
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $final_fields,
            CURLOPT_RETURNTRANSFER => true,
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

}