<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Pagostt {

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

}