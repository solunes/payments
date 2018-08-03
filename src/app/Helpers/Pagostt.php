<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Pagostt {

    public static function generateAppKey() {
        $token = \Pagostt::generateToken([8,4,4,4,12]);
        return $token;
    }

    public static function putInoviceParameters($transaction) {
        $save = false;
        if(request()->has('nit_company')){
            $transaction->nit_company = urldecode(request()->input('nit_company'));
            $save = true;
        }
        if(request()->has('invoice_number')){
            $transaction->invoice_number = request()->input('invoice_number');
            $save = true;
        }
        if(request()->has('auth_number')){
            $transaction->auth_number = request()->input('auth_number');
            $save = true;
        }
        if(request()->has('control_code')){
            $transaction->control_code = urldecode(request()->input('control_code'));
            $save = true;
        }
        if(request()->has('customer_name')){
            $transaction->customer_name = urldecode(request()->input('customer_name'));
            $save = true;
        }
        if(request()->has('customer_nit')){
            $transaction->customer_nit = request()->input('customer_nit');
            $save = true;
        }
        if(request()->has('invoice_type')){
            $transaction->invoice_type = request()->input('invoice_type');
            $save = true;
        }
        if(request()->has('invoice_id')){
            $transaction->invoice_id = request()->input('invoice_id');
            $save = true;
        }
        if(config('payments.pagostt_params.enable_cycle')&&$transaction->invoice_type=='C'){
            if(request()->has('billing_cycle_dosage')){
                $dosage_decrypt = \Pagostt::pagosttDecrypt(request()->input('billing_cycle_dosage'));
                $transaction->billing_cycle_dosage = $dosage_decrypt;
                $save = true;
            }
            if(request()->has('billing_cycle_start_date')){
                $transaction->billing_cycle_start_date = request()->input('billing_cycle_start_date');
                $save = true;
            }
            if(request()->has('billing_cycle_end_date')){
                $transaction->billing_cycle_end_date = request()->input('billing_cycle_end_date');
                $save = true;
            }
            if(request()->has('billing_cycle_eticket')){
                $transaction->billing_cycle_eticket = request()->input('billing_cycle_eticket');
                $save = true;
            }
            if(request()->has('billing_cycle_legend')){
                $transaction->billing_cycle_legend = request()->input('billing_cycle_legend');
                $save = true;
            }
            if(request()->has('billing_cycle_parallel')){
                $transaction->billing_cycle_parallel = request()->input('billing_cycle_parallel');
                $save = true;
            }
            if(request()->has('billing_cycle_invoice_title')){
                $transaction->billing_cycle_invoice_title = request()->input('billing_cycle_invoice_title');
                $save = true;
            }
            if(request()->has('company_code')){
                $transaction->company_code = request()->input('company_code');
                $save = true;
            }
        }
        return ['transaction'=>$transaction,'save'=>$save];
    }

    public static function generatePaymentItem($concept, $quantity, $cost, $invoice = true) {
        $item = [];
        $item['concepto'] = $concept;
        $item['cantidad'] = $quantity;
        $item['costo_unitario'] = $cost;
        if($invoice==false){
            $item['ignorar_factura'] = true;
        }
        $encoded_item = json_encode($item);
        return $encoded_item;
    }

    public static function generatePaymentMetadata($nombre, $dato) {
        $item = [];
        $item['nombre'] = $nombre;
        $item['dato'] = $dato;
        $encoded_item = json_encode($item);
        return $encoded_item;
    }

    public static function generatePaymentTransaction($customer_id, $payment_ids, $amount = NULL) {
        $payment_code = \Pagostt::generatePaymentCode();
        $transaction = new \Solunes\Payments\App\Transaction;
        $transaction->payment_code = $payment_code;
        $transaction->payment_method_id = 2;
        $transaction->save();
        foreach($payment_ids as $payment_id){
            $transaction_payment = new \Solunes\Payments\App\TransactionPayment;
            $transaction_payment->parent_id = $transaction->id;
            $transaction_payment->payment_id = $payment_id;
            $transaction_payment->save();
        }
        return $transaction;
    }

    public static function generatePaymentCode() {
        $token = \Pagostt::generateToken([8,4,4,4,12]);
        if(\Solunes\Payments\App\Transaction::where('payment_code', $token)->first()){
            $token = \Pagostt::generatePaymentCode();
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

    public static function generateTransactionArray($customer, $payment, $transaction, $custom_app_key = NULL) {
        $callback_url = \Pagostt::generatePaymentCallback($transaction->payment_code);
        if($custom_app_key&&config('payments.pagostt_params.custom_app_keys.'.$custom_app_key)){
            $app_key = config('payments.pagostt_params.custom_app_keys.'.$custom_app_key);
        } else {
            $app_key = config('payments.pagostt_params.app_key');
        }
        if(config('payments.pagostt_params.finish_payment_verification')){
            $payment = \PagosttBridge::finishPaymentVerification($payment, $transaction);
        }
        $final_fields = array(
            "appkey" => $app_key,
            "email_cliente" => $customer['email'],
            "callback_url" => $callback_url,
            "razon_social" => $customer['nit_name'],
            "nit" => $customer['nit_number'],
        );
        if(isset($customer['ci_number'])){
            $final_fields['ci'] = $customer['ci_number'];
        }
        if(isset($customer['first_name'])){
            $final_fields['nombre_cliente'] = $customer['first_name'];
        }
        if(isset($customer['last_name'])){
            $final_fields['apellido_cliente'] = $customer['last_name'];
        }
        if(isset($payment['has_invoice'])){
            $final_fields['emite_factura'] = $payment['has_invoice'];
        }
        if(isset($payment['shipping_amount'])){
            $final_fields['valor_envio'] = $payment['shipping_amount'];
        } else {
            $final_fields['valor_envio'] = 0;
        }
        if(isset($payment['shipping_detail'])){
            $final_fields['descripcion_envio'] = $payment['shipping_detail'];
        } else {
            $final_fields['descripcion_envio'] = "Costo de envío no definido.";

        }
        $final_fields['descripcion'] = $payment['name'];
        $final_fields['lineas_detalle_deuda'] = $payment['items'];
        if(isset($payment['metadata'])){
            $final_fields['lineas_metadatos'] = $payment['metadata'];
        }
        return $final_fields;
    }

    public static function generateTransactionQuery($transaction, $final_fields) {
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
            \Log::info('Error en PagosTT: '.json_encode($decoded_result));
            return NULL;
        }

        // Guardado de transaction_id generado por PagosTT
        $transaction_id = $decoded_result->id_transaccion;
        $transaction->external_payment_code = $transaction_id;
        $transaction->save();
        
        // URL para redireccionar
        $api_url = $decoded_result->url_pasarela_pagos;
        return $api_url;
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

    public static function encrypt($plainTextToEncrypt) {
        $secret_key = config('payments.pagostt_params.salt');
        $secret_iv = config('payments.pagostt_params.secret_iv');
          
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
     
        $output = base64_encode( openssl_encrypt( $plainTextToEncrypt, $encrypt_method, $key, 0, $iv ) );
        return $output;
    }
    
    public static function decrypt($textToDecrypt) {
        $secret_key = config('payments.pagostt_params.salt');
        $secret_iv = config('payments.pagostt_params.secret_iv');
     
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
     
        $output = openssl_decrypt( base64_decode( $textToDecrypt ), $encrypt_method, $key, 0, $iv );
        return $output;
    }

    public static function pagosttEncrypt($plainTextToEncrypt) {
        $key = config('payments.pagostt_params.secret_pagostt_iv');
        $ivArray=array( 0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF  );
        $iv=null;
        $block = mcrypt_get_block_size('des', 'cbc');
        $pad = $block - (strlen($plainTextToEncrypt) % $block);
        $plainTextToEncrypt .= str_repeat(chr($pad), $pad);
        foreach ($ivArray as $element){
            $iv.=CHR($element);
        }
        $encrypted_string = mcrypt_encrypt(MCRYPT_DES, $key, $plainTextToEncrypt, MCRYPT_MODE_CBC, $iv);
        return strtr(base64_encode($encrypted_string), '+/=', '._-');
    }

    public static function pagosttDecrypt($plainTextToDecrypt) {
        $key = config('payments.pagostt_params.secret_pagostt_iv');
        $ivArray=array( 0x12, 0x34, 0x56, 0x78, 0x90, 0xAB, 0xCD, 0xEF  );
        $iv=null;
        foreach ($ivArray as $element){
            $iv.=CHR($element);
        }
        $plainTextToDecrypt = base64_decode(strtr($plainTextToDecrypt, '._-', '+/='));
        $decrypted_string = mcrypt_decrypt(MCRYPT_DES, $key, $plainTextToDecrypt, MCRYPT_MODE_CBC, $iv);
        $block = mcrypt_get_block_size('des', 'cbc');
        $pad = ord($decrypted_string[strlen($decrypted_string)-1]);
        $response = substr($decrypted_string, 0, strlen($decrypted_string) - $pad);
        return $response;
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
        $final_fields['app_key'] = config('payments.pagostt_params.app_key');
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