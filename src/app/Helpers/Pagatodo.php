<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Pagatodo {

    public static function generateSalePayment($payment_item, $cancel_url) {
        $custom_app_key = NULL;
        if(config('payments.pagatodo_params.enable_bridge')){
            $customer = \PagatodoBridge::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \PagatodoBridge::getPayment($payment_item->id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \Customer::getPayment($payment_item->id, $custom_app_key);
        }
        if($customer&&$payment){
          $payment = \Payments::getShippingCost($payment, [$payment_item->id]);
          $pagostt_transaction = \Pagatodo::generatePaymentTransaction($payment_item->customer_id, [$payment_item->id], $payment['amount']);
          $final_fields = \Pagatodo::generateTransactionArray($customer, $payment, $pagostt_transaction, $custom_app_key);
          $api_url = \Pagatodo::generateTransactionQuery($pagostt_transaction, $final_fields);
          if($api_url){
            return $api_url;
          } else {
            return NULL;
          }
        } else {
          \Log::info('Error, no hay Customer ('.json_encode($customer).') y Payment ('.json_encode($payment).')');
          return NULL;
        }
    }

    public static function getSession($apptoken = NULL, $custom_key = NULL) {
        if(!$apptoken){
            $appuser = NULL;
            $apppassword = NULL;
            if($custom_key){
                if(config('payments.pagatodo_params.testing')==true&&config('payments.pagatodo_params.custom_users.'.$custom_key)&&config('payments.pagatodo_params.custom_passwords.'.$custom_key)){
                    $appuser = config('payments.pagatodo_params.custom_test_users.'.$custom_key);
                    $apppassword = config('payments.pagatodo_params.custom_test_passwords.'.$custom_key);
                } else if(config('payments.pagatodo_params.testing')==false&&config('payments.pagatodo_params.custom_test_users.'.$custom_key)&&config('payments.pagatodo_params.custom_test_passwords.'.$custom_key)) {
                    $appuser = config('payments.pagatodo_params.custom_users.'.$custom_key);
                    $apppassword = config('payments.pagatodo_params.custom_passwords.'.$custom_key);
                }
            }
            if(!$apptoken){
                if(config('payments.pagatodo_params.testing')){
                    $appuser = config('payments.pagatodo_params.test_user');
                    $apppassword = config('payments.pagatodo_params.test_pass');
                } else {
                    $appuser = config('payments.pagatodo_params.user');
                    $apppassword = config('payments.pagatodo_params.pass');
                }
            }
            $apptoken = \Pagatodo::getSessionQuery($appuser, $apppassword);
        }
        return $apptoken;
    }

    public static function getSessionQuery($appuser, $apppassword) {
        $url = \Pagatodo::queryTransactiontUrl('SessionRest');
        $final_fields = ['user'=>$appuser, 'pass'=>$apppassword];
        \Log::info('final_fields: '.json_encode($final_fields));
        $response = \Pagatodo::queryCurlTransaction($url, $final_fields);
        \Log::info(json_encode($response));
        if($response->token){
            return $response->token;
        }
        return NULL;
    }

    public static function getCompanyId($idempresa = NULL) {
        if(!$idempresa){
            if(config('payments.pagatodo_params.testing')==true){
                $idempresa = config('payments.pagatodo_params.idempresa_test');
            } else if(config('payments.pagatodo_params.testing')==false) {
                $idempresa = config('payments.pagatodo_params.idempresa');
            }
        }
        return $idempresa;
    }

    public static function transformCurrency($amount, $currency_exchange) {
        $amount = $amount * $currency_exchange;
        return $amount;
    }
    
    public static function generatePaymentItem($concept, $quantity, $cost, $invoice = true, $extra_parameters = []) {
        $item = [];
        //$concept = preg_replace('/[^A-Za-z0-9\-\(\) ]/', '', $concept); //removes ALL characters
        $item['concepto'] = $concept;
        $item['cantidad'] = $quantity;
        $item['costo_unitario'] = $cost;
        if($invoice==false){
            $item['ignorar_factura'] = true;
        }
        foreach($extra_parameters as $extra_key => $extra_parameter){
            $item[$extra_key] = $extra_parameter;
        }
        $encoded_item = json_encode($item);
        return $encoded_item;
    }

    public static function generatePaymentTransaction($customer_id, $payment_ids, $amount = NULL) {
        $payment_code = \Pagatodo::generatePaymentCode();
        $payment_method = \Solunes\Payments\App\PaymentMethod::where('code', 'pagatodo')->first();
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

    public static function generatePaymentCode() {
        $token = \Pagatodo::generateToken([8,4,4,4,12]);
        if(\Solunes\Payments\App\Transaction::where('payment_code', $token)->first()){
            $token = \Pagatodo::generatePaymentCode();
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

    public static function generateTransactionArray($customer, $payment, $transaction, $custom_apptoken = 'default', $apptoken = NULL) {
        $success_callback_url = \Pagatodo::generateRedirectCallback('success',$transaction->id);
        $error_callback_url = \Pagatodo::generateRedirectCallback('error',$transaction->id);
        $callback_url = \Pagatodo::generatePaymentCallback();
        $apptoken = \Pagatodo::getSession($apptoken, $custom_apptoken);
        $transaction->external_payment_code = $apptoken;
        $transaction->save();
        if(config('payments.pagatodo_params.finish_payment_verification')){
            $payment = \PagatodoBridge::finishPaymentVerification($payment, $transaction);
        }
        if(isset($payment['nit_name'])){
            $nit_name = $payment['nit_name'];
        } else if(isset($customer['nit_name'])){
            $nit_name = $customer['nit_name'];
        } else {
            $nit_name = 'Sin Nombre';
        }
        if(isset($payment['nit_number'])){
            $nit_number = $payment['nit_number'];
        } else if(isset($customer['nit_number'])){
            $nit_number = $customer['nit_number'];
        } else {
            $nit_number = 0;
        }
        $final_fields = array(
            "token" => $apptoken,
            "correo" => $customer['email'],
            "idempresa" => \Pagatodo::getCompanyId(),
            "descripcion_general" => $nit_name, // CAMBIAR POR RECIBO O PAYMENT ID
            "nro_recibo" => $transaction->id, // CAMBIAR POR TRANSACTION ID
            "nit" => $nit_number,
            "pdata01" => $transaction->payment_code,
            "pdata02" => $callback_url,
            "pdata03" => $success_callback_url,
            //"pdata04" => $error_callback_url, // FECHA DE VENCIMIENTO
            //"pdata05" => $error_callback_url,
        );
        if(isset($payment['customer_ci_number'])){
            $final_fields['tipo_documento'] = 1;
            $final_fields['valor_documento'] = $payment['customer_ci_number'];
        } else if(isset($customer['ci_number'])){
            $final_fields['tipo_documento'] = 1;
            $final_fields['valor_documento'] = $customer['ci_number'];
        } else {
            $final_fields['tipo_documento'] = 1;
            $final_fields['valor_documento'] = 0;
        }
        if(isset($customer['customer_code'])){
            $final_fields['codigo_cliente'] = $customer['customer_code'];
        }
        if(isset($customer['first_name'])){
            $final_fields['nombres'] = $customer['first_name'];
        }
        if(isset($customer['last_name'])){
            $final_fields['apellido_paterno'] = $customer['last_name'];
        }
        if(isset($payment['shipping_detail'])){
            $shipping_detail = $payment['shipping_detail']; //removes ALL characters
            $final_fields['domicilio'] = $shipping_detail;
        } else {
            $final_fields['domicilio'] = "Sin dirección de envío.";
        }
        $final_fields['concepto_recibo'] = $payment['name'];
        $detalle = [];
        $total = 0;
        foreach($payment['items'] as $payment_item){
            \Log::info('detalle: '.$payment_item);
            $decode_payment_item = json_decode($payment_item, true);
            $total += $decode_payment_item['costo_unitario']*$decode_payment_item['cantidad'];
            $detalle[] = ['descripcion_item'=>$decode_payment_item['concepto'],'cantidad'=>$decode_payment_item['cantidad'],'item'=>$decode_payment_item['concepto'],'precio_unitario'=>round($decode_payment_item['costo_unitario'],2),'sub_total'=>round($decode_payment_item['costo_unitario']*$decode_payment_item['cantidad'],2)];
        }
        $final_fields['detalle'] = $detalle;
        $final_fields['moneda'] = 1;
        $final_fields['monto'] = $total;
        if(config('payments.pagatodo_params.enable_custom_func')){
            $final_fields = \CustomFunc::pagatodo_params($final_fields, $customer, $payment, $transaction, $custom_app_key, $app_key, $cashier_app_key);
        }
        \Log::info(json_encode($final_fields));
        return $final_fields;
    }

    public static function generateTransactionQuery($transaction, $final_fields) {
        $url = \Pagatodo::queryTransactiontUrl('PagoRest');
        $decoded_result = \Pagatodo::queryCurlTransaction($url, $final_fields);
        \Log::info('decoded_result: '.json_encode($decoded_result)); // OCULTAR
        // Guardado de transaction_id generado por PagosTT
        //$transaction->external_payment_code = rand(10000,99999); // DEFINIR SI GUARDAR ALGO
        $transaction->save();
        
        // URL para redireccionar
        $api_url = $decoded_result->URL;
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

    public static function generateRedirectCallback($status, $transaction_id) {
        $url = url('api/pagatodo-redireccion/'.$status.'/'.$transaction_id);
        return $url;
    }

    public static function generatePaymentCallback() {
        $url = url('api/pagatodo-callback');
        return $url;
    }

    public static function queryTransactiontUrl($action) {
        if(config('payments.pagatodo_params.testing')){
            $url = config('payments.pagatodo_params.test_server');
        } else {
            $url = config('payments.pagatodo_params.main_server');
        }
        $url .= $action;
        return $url;
    }

    public static function queryCurlTransaction($url, $final_fields) {
        $ch = curl_init();
        /*curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($final_fields) );*/

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($final_fields));

        $headers = array();
        $headers[] = 'Accept: */*';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        \Log::info(json_encode($url));
        curl_close($ch);  

        // Decodificar resultado
        $decoded_result = json_decode($result);
        return $decoded_result;
    }

    public static function generateTestingPayment() {
        $customer = ['email'=>'edumejia30@gmail.com','nit_name'=>'Mejia','nit_number'=>'4768578017','ci_number'=>'4768578','first_name'=>'Eduardo','first_name'=>'Eduardo','last_name'=>'Mejia'];
        $payment_lines = [\Pagatodo::generatePaymentItem('Pago por muestra 1', 1, 100), \Pagatodo::generatePaymentItem('Pago por muestra 2', 1, 100)];
        $payment = ['has_invoice'=>1,'name'=>'Pago de muestra 1','items'=>$payment_lines];
        $pagostt_transaction = \Pagatodo::generatePaymentTransaction(1, [1], 200);
        $final_fields = \Pagatodo::generateTransactionArray($customer, $payment, $pagostt_transaction);
        $api_url = \Pagatodo::generateTransactionQuery($pagostt_transaction, $final_fields);
        return $api_url;
    }

}