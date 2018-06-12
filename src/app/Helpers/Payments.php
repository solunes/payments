<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Payments {

    public static function generateSalePayment($sale, $model, $redirect) {
        $payment = \Payments::generatePayment($sale);
        $cancel_url = url('payments/finish-payment/'.$payment->id);

        $model = new $model;
        $api_url = $model->generateSalePayment($payment, $cancel_url);
        if($api_url){
            return redirect($api_url);
        } else {
            return redirect($redirect)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
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
        $item['id'] = $sale->user_id;
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
        $currency = \Solunes\Business\App\Currency::find(2);
        $payment = new \Solunes\Payments\App\Payment;
        $payment->customer_id = $sale->user_id;
        $payment->name = 'Compra de productos online';
        $payment->customer_name = $sale->user->name;
        $payment->customer_email = $sale->user->email;
        $payment->invoice = $sale->invoice;
        $payment->invoice_name = $sale->invoice_name;
        $payment->invoice_number = $sale->invoice_number;
        $payment->amount = \Business::calculate_currency($sale->amount, $currency, $sale->currency);
        $payment->currency_id = $currency->id;
        $payment->status = 'holding';
        $payment->save();
        $sale->load('sale_items');
        foreach($sale->sale_items as $sale_item){
            $payment_item = new \Solunes\Payments\App\PaymentItem;
            $payment_item->parent_id = $payment->id;
            $payment_item->item_type = 'sale-item';
            $payment_item->item_id = $sale_item->id;
            $payment_item->name = $sale_item->name;
            $payment_item->currency_id = $payment->currency_id;
            $payment_item->quantity = $sale_item->quantity;
            $payment_item->price = \Business::calculate_currency($sale_item->price, $payment->currency, $sale_item->currency);
            $payment_item->save();
        }
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
            $payment_shipping = new \Solunes\Payments\App\PaymentShipping;
            $payment_shipping->parent_id = $payment->id;
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
        return $payment;
    }

    public static function generatePaymentTransaction($payment, $payment_method_code) {
        $payment_method = \Solunes\Payments\App\PaymentMethod::where('code', $payment_method_code)->first();
        if($payment_method){
            $payment_code = \Payments::generatePaymentCode();
            $transaction = new \Solunes\Payments\App\Transaction;
            $transaction->parent_id = $payment->id;
            $transaction->payment_code = $payment_code;
            $transaction->payment_method_id = $payment_method->id;
            $transaction->save();
            $transaction_payment = new \Solunes\Payments\App\TransactionPayment;
            $transaction_payment->parent_id = $transaction->id;
            $transaction_payment->payment_id = $payment->id;
            $transaction_payment->save();
            return $transaction;
        }
        return false;
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

    public static function generatePaymentCallback($payment_code, $external_payment_code = NULL) {
        $url = url('api/confirmed-payment/'.$payment_code);
        if($external_payment_code){
            $url .= '/'.$external_payment_code.'?external_payment_code='.$external_payment_code;
        }
        return $url;
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