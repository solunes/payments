<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Pagostt {

    public static function generateSalePayment($payment, $cancel_url) {
        $payments_transaction = \Payments::generatePaymentTransaction($payment, 'pagostt');
        $callback_url = \Payments::generatePaymentCallback($payments_transaction->payment_code);
        $final_fields = \Solunes\Payments\App\Helpers\Pagostt::generateTransactionArray($callback_url, $payment, $payments_transaction);
        $api_url = \Solunes\Payments\App\Helpers\Pagostt::generateTransactionQuery($payments_transaction, $final_fields);
        if($api_url){
            return $api_url;
        } else {
            return NULL;
        }
    }

    public static function generateTransactionArray($callback_url, $payment) {
        $shipping_desc = "Sin costo de envío";
        $shipping_price = 0;
        if(config('payments.shipping')){
            $payment->load('payment_shippings');
            foreach($payment->payment_shippings as $shipping){
                $shipping_desc = $shipping->name;
                $shipping_price = $shipping->price;
            }
        }
        $final_fields = array(
            "appkey" => config('payments.pagostt_app_key'),
            "email_cliente" => $payment->customer_email,
            "callback_url" => $callback_url,
            "razon_social" => $payment->invoice_name,
            "nit" => $payment->invoice_number,
            "valor_envio" => $shipping_price,
            "descripcion_envio" => $shipping_desc,
        );
        $final_fields['descripcion'] = $payment->name;
        $items_array = [];
        $payment->load('payment_items');
        foreach($payment->payment_items as $payment_item){
            $item = [];
            $item['concepto'] = $payment_item->name;
            $item['cantidad'] = $payment_item->quantity;
            $item['costo_unitario'] = $payment_item->price;
            $item['factura_independiente'] = $payment->invoice;
            $encoded_item = json_encode($item);
            $items_array[] = $encoded_item;
        }
        $final_fields['lineas_detalle_deuda'] = $items_array;
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
            \Log::info(json_encode($decoded_result));
            return NULL;
        }

        // Guardado de transaction_id generado por PagosTT
        $transaction_id = $decoded_result->id_transaccion;
        $payments_payment->external_payment_code = $transaction_id;
        $payments_payment->save();
        
        // URL para redireccionar
        $api_url = $decoded_result->url_pasarela_pagos;
        return $api_url;
    }

}