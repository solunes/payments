<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Paypal {

    public static function generateAppKey() {
        $token = \Payments::generateToken([8,4,4,4,12]);
        return $token;
    }

    public static function generatePaymentItem($concept, $quantity, $cost, $invoice = true) {
        $item = [];
        $item['concepto'] = $concept;
        $item['cantidad'] = $quantity;
        $item['costo_unitario'] = $cost;
        $item['factura_independiente'] = $invoice;
        $encoded_item = json_encode($item);
        return $encoded_item;
    }

    public static function generatePaymentTransaction($customer_id, $payment_ids, $amount = NULL) {
        $payment_code = \Payments::generatePaymentCode();
        $payments_transaction = new \Solunes\Payments\App\PttTransaction;
        $payments_transaction->customer_id = $customer_id;
        $payments_transaction->payment_code = $payment_code;
        $payments_transaction->amount = $amount;
        $payments_transaction->status = 'holding';
        $payments_transaction->save();
        foreach($payment_ids as $payment_id){
            $payments_payment = new \Solunes\Payments\App\PttTransactionPayment;
            $payments_payment->parent_id = $payments_transaction->id;
            $payments_payment->payment_id = $payment_id;
            $payments_payment->save();
        }
        return $payments_transaction;
    }

    public static function generatePaymentCode() {
        $token = \Payments::generateToken([8,4,4,4,12]);
        if(\Solunes\Payments\App\PttTransaction::where('payment_code', $token)->first()){
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

    public static function generateTransactionArray($customer, $payment, $payments_transaction) {
        $callback_url = \Payments::generatePaymentCallback($payments_transaction->payment_code);
        $final_fields = array(
            "appkey" => config('payments.app_key'),
            "email_cliente" => $customer['email'],
            "callback_url" => $callback_url,
            "razon_social" => $customer['nit_name'],
            "nit" => $customer['nit_number'],
            "valor_envio" => 0,
            "descripcion_envio" => "Sin costo de envío",
        );
        $final_fields['descripcion'] = $payment['name'];
        $final_fields['lineas_detalle_deuda'] = $payment['items'];
        return $final_fields;
    }

    public static function generateTransactionQuery($payments_payment, $final_fields) {
        // Consulta CURL a Web Service
        $url = 'http://www.todotix.com:10365/rest/deuda/registrar';
        $ch = curl_init();
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($final_fields),
            CURLOPT_RETURNTRANSFER => true,
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        curl_close($ch);  

        // Decodificar resultado
        $decoded_result = json_decode($result);
        
        if(!isset($decoded_result->url_pasarela_pagos)){
            return NULL;
        }

        // Guardado de transaction_id generado por PagosTT
        $transaction_id = $decoded_result->id_transaccion;
        $payments_payment->transaction_id = $transaction_id;
        $payments_payment->save();
        
        // URL para redireccionar
        $api_url = $decoded_result->url_pasarela_pagos;
        return $api_url;
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

    public static function generatePaymentCallback($payment_code, $transaction_id = NULL) {
        $url = url('api/pago-confirmado/'.$payment_code);
        if($transaction_id){
            $url .= '/'.$transaction_id.'?transaction_id='.$transaction_id;
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