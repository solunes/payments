<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Payments {

    public static function getSalePaymentBridge($sale) {
        $item['id'] = $sale->id;
        $item['name'] = $sale->name;
        $subitems_array = [];
        foreach($sale->sale_items as $payment_item){
            $subitems_array[] = \Pagostt::generatePaymentItem($payment_item->name, $payment_item->quantity, $payment_item->amount, 0);
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

    public static function generatePayment($customer_id, $sale, $amount) {
        $payment = new \Solunes\Payments\App\Payment;
        $payment->customer_id = $customer_id;
        $payment->amount = $amount;
        $payment->status = 'holding';
        $payment->save();
        foreach($sale->sale_items as $sale_item){
            $payment_item = new \Solunes\Payments\App\PaymentItem;
            $payment_item->parent_id = $payments_transaction->id;
            $payment_item->item_type = 'sale-item';
            $payment_item->item_id = $payment_item->id;
            $payment_item->name = $sale_item->name;
            $payment_item->currency_id = $sale_item->currency_id;
            $payment_item->quantity = $sale_item->quantity;
            $payment_item->price = $sale_item->price;
            $payment_item->save();
        }

        return $payment;
    }

    public static function generatePaymentTransaction($payment, $payment_method_id) {
        $payment_code = \Payments::generatePaymentCode();
        $payment_transaction = new \Solunes\Payments\App\PaymentTransaction;
        $payment_transaction->parent_id = $payment->id;
        $payment_transaction->payment_code = $payment_code;
        $payment_transaction->payment_method_id = $payment_method_id;
        $payment_transaction->save();
        return $payment_transaction;
    }

    public static function generatePaymentCode() {
        $token = \Payments::generateToken([8,4,4,4,12]);
        if(\Solunes\Payments\App\PaymentTransaction::where('payment_code', $token)->first()){
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