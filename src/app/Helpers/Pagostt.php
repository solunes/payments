<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Pagostt {

    public static function generateSalePayment($payment_item, $cancel_url, $app_payment = false) {
        $custom_app_key = NULL;
        if(config('payments.pagostt_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \PagosttBridge::getPayment($payment_item->id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \Customer::getPayment($payment_item->id, $custom_app_key);
        }
        if($customer&&$payment){
          $payment = \Payments::getShippingCost($payment, [$payment_item->id]);
          $pagostt_transaction = \Pagostt::generatePaymentTransaction($payment_item->customer_id, [$payment_item->id], $payment['amount']);
          $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_transaction, $custom_app_key);
          $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
          if($api_url){
            if($app_payment){
                $callback_url = \Pagostt::generatePaymentCallback($pagostt_transaction->payment_code);
                return ['payment_url'=>$api_url, 'success_callback_page'=>$callback_url];
            }
            return $api_url;
          } else {
            return NULL;
          }
        } else {
          \Log::info('Error, no hay Customer ('.json_encode($customer).') y Payment ('.json_encode($payment).')');
          return NULL;
        }
    }

    public static function generateAppKey() {
        $token = \Pagostt::generateToken([8,4,4,4,12]);
        return $token;
    }

    public static function putInoviceParameters($transaction) {
        $transaction_invoice = $transaction->transaction_invoice;
        if(!$transaction_invoice){
            $transaction_invoice = new \Solunes\Payments\App\TransactionInvoice;
            $transaction_invoice->parent_id = $transaction->id;
            $transaction_invoice->amount = $transaction->amount;
        }
        $save = false;
        if(request()->has('nit_company')){
            $transaction_invoice->nit_company = urldecode(request()->input('nit_company'));
            $save = true;
        }
        if(request()->has('invoice_number')){
            $transaction_invoice->invoice_number = request()->input('invoice_number');
            $save = true;
        }
        if(request()->has('auth_number')){
            $transaction_invoice->auth_number = request()->input('auth_number');
            $save = true;
        }
        if(request()->has('control_code')){
            $transaction_invoice->control_code = urldecode(request()->input('control_code'));
            $save = true;
        }
        if(request()->has('customer_name')){
            $transaction_invoice->customer_name = urldecode(request()->input('customer_name'));
            $save = true;
        }
        if(request()->has('customer_nit')){
            $transaction_invoice->customer_nit = request()->input('customer_nit');
            $save = true;
        }
        if(request()->has('invoice_type')){
            $transaction_invoice->invoice_type = request()->input('invoice_type');
            $save = true;
        }
        if(request()->has('invoice_id')){
            $transaction_invoice->invoice_code = request()->input('invoice_id');
            $save = true;
        }
        if(request()->has('invoice_url')){
            $transaction_invoice->invoice_url = request()->input('invoice_url');
            $save = true;
        }
        if(config('payments.pagostt_params.enable_cycle')&&$transaction_invoice->invoice_type=='C'){
            if(request()->has('billing_cycle_dosage')){
                $dosage_decrypt = \Pagostt::pagosttDecrypt(request()->input('billing_cycle_dosage'));
                $transaction_invoice->billing_cycle_dosage = $dosage_decrypt;
                $save = true;
            }
            if(request()->has('billing_cycle_start_date')){
                $transaction_invoice->billing_cycle_start_date = request()->input('billing_cycle_start_date');
                $save = true;
            }
            if(request()->has('billing_cycle_end_date')){
                $transaction_invoice->billing_cycle_end_date = request()->input('billing_cycle_end_date');
                $save = true;
            }
            if(request()->has('billing_cycle_eticket')){
                $transaction_invoice->billing_cycle_eticket = request()->input('billing_cycle_eticket');
                $save = true;
            }
            if(request()->has('billing_cycle_legend')){
                $transaction_invoice->billing_cycle_legend = request()->input('billing_cycle_legend');
                $save = true;
            }
            if(request()->has('billing_cycle_parallel')){
                $transaction_invoice->billing_cycle_parallel = request()->input('billing_cycle_parallel');
                $save = true;
            }
            if(request()->has('billing_cycle_invoice_title')){
                $transaction_invoice->billing_cycle_invoice_title = request()->input('billing_cycle_invoice_title');
                $save = true;
            }
            if(request()->has('company_code')){
                $transaction_invoice->company_code = request()->input('company_code');
                $save = true;
            }
        }
        if($save){
            $transaction_invoice->save();
        }
        return $save;
    }

    public static function putPaymentInvoice($transaction) {
        $transaction->load('transaction_invoice');
        if($transaction_invoice = $transaction->transaction_invoice){
            $transaction->load('transaction_payments');
            foreach($transaction->transaction_payments as $transaction_payment){
                $payment = $transaction_payment->payment;
                if($payment->invoice){
                    if(!$payment_invoice = $payment->payment_invoice){
                        $payment_invoice = new \Solunes\Payments\App\PaymentInvoice;
                        $payment_invoice->parent_id = $payment->id;
                    }
                    $payment_invoice->name = 'Factura de: '.$payment->name;
                    $payment_invoice->invoice_code = $transaction_invoice->invoice_code;
                    $payment_invoice->invoice_url = $transaction_invoice->invoice_url;
                    $payment_invoice->invoice_number = $transaction_invoice->invoice_number;
                    $payment_invoice->customer_name = $transaction_invoice->customer_name;
                    $payment_invoice->customer_nit = $transaction_invoice->customer_nit;
                    $payment_invoice->amount = $transaction_invoice->amount;
                    $payment_invoice->save();
                }
            }
        }
    }

    public static function putInoviceParametersCashier($transaction, $factura_electronica) {
        $transaction_invoice = $transaction->transaction_invoice;
        if(!$transaction_invoice){
            $transaction_invoice = new \Solunes\Payments\App\TransactionInvoice;
            $transaction_invoice->parent_id = $transaction->id;
            $transaction_invoice->amount = $transaction->amount;
        }
        $transaction_invoice->nit_company = $factura_electronica->nit;
        $transaction_invoice->invoice_number = $factura_electronica->numero_factura;
        $transaction_invoice->auth_number = $factura_electronica->numero_autorizacion;
        $transaction_invoice->control_code = $factura_electronica->codigo_control;
        $transaction_invoice->customer_name = $factura_electronica->cliente_razon_social;
        $transaction_invoice->customer_nit = $factura_electronica->cliente_nit;
        $transaction_invoice->invoice_type = $factura_electronica->tipo_dosificacion;
        $transaction_invoice->invoice_code = $factura_electronica->identificador;
        if(config('payments.pagostt_params.testing')){
            $transaction_invoice->invoice_url = config('payments.pagostt_params.invoice_test_server').$factura_electronica->identificador;
        } else {
            $transaction_invoice->invoice_url = config('payments.pagostt_params.invoice_server').$factura_electronica->identificador;
        }
        if(config('payments.pagostt_params.enable_cycle')&&$ptt_transaction->invoice_type=='C'){
            // TODO REVISAR
            if($factura_electronica->dosificacion){
                $dosage_decrypt = \Pagostt::pagosttDecrypt($factura_electronica->dosificacion);
                $transaction_invoice->billing_cycle_dosage = $dosage_decrypt;
            }
            $transaction_invoice->billing_cycle_start_date = $factura_electronica->fecha_inicio_ciclo;
            $transaction_invoice->billing_cycle_end_date = $factura_electronica->fecha_final_ciclo;
            $transaction_invoice->billing_cycle_eticket = $factura_electronica->eticket_dosificacion;
            $transaction_invoice->billing_cycle_legend = $factura_electronica->leyenda_dosificacion;
            $transaction_invoice->billing_cycle_parallel = $factura_electronica->paralela_dosificacion;
        }
        $transaction_invoice->save();
        return true;
    }

    public static function getItemExtraParameters($payment_item) {
        $array = [];
        if(config('payments.sfv_version')>1){
            $array['codigo_actividad_economica'] = $payment_item->economic_sin_activity;
            $array['codigo_sin'] = $payment_item->product_sin_code;
            $array['codigo_interno'] = $payment_item->product_internal_code;
            $array['serie_producto'] = $payment_item->product_serial_number;
        }
        if(config('payments.sfv_version')>1||config('payments.discounts')){
            $array['descuento_unitario'] = $payment_item->discount_price;
            $array['descuento_total'] = $payment_item->discount_amount;
        }
        return $array;
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

    public static function paymentAddPaymentInvoice($payment_array, $payment) {
        if($payment||config('payments.sfv_version')>1){
            $payment_array['commerce_user_code'] = $payment->commerce_user_code;
            $payment_array['customer_code'] = $payment->customer_code;
            $payment_array['customer_ci_number'] = $payment->customer_ci_number;
            $payment_array['customer_ci_extension'] = $payment->customer_ci_extension;
            $payment_array['customer_ci_expedition'] = $payment->customer_ci_expedition;
            $payment_array['invoice_type'] = $payment->invoice_type;
            $payment_array['payment_type_code'] = $payment->payment_type_code;
            $payment_array['card_number'] = $payment->card_number;
        }
        return $payment_array;
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
        $transaction->customer_id = $customer_id;
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

    public static function generateTransactionArray($customer, $payment, $transaction, $custom_app_key = 'default', $app_key = NULL, $cashier_app_key = NULL) {
        $callback_url = \Pagostt::generatePaymentCallback($transaction->payment_code);
        $app_key = \Pagostt::getAppKey($app_key, $custom_app_key);
        if(config('payments.pagostt_params.finish_payment_verification')){
            $payment = \PagosttBridge::finishPaymentVerification($payment, $transaction);
            if(isset($payment['cancel_payment'])){
                return $payment;
            }
        }
        if(!config('payments.pagostt_params.use_customer_invoice_data')&&isset($payment['nit_name'])){
            $nit_name = $payment['nit_name'];
        } else if(isset($customer['nit_name'])){
            $nit_name = $customer['nit_name'];
        } else {
            $nit_name = 'Sin Nombre';
        }
        if(!config('payments.pagostt_params.use_customer_invoice_data')&&isset($payment['nit_number'])){
            $nit_number = $payment['nit_number'];
        } else if(isset($customer['nit_number'])){
            $nit_number = $customer['nit_number'];
        } else {
            $nit_number = 0;
        }
        $final_fields = array(
            "appkey" => $app_key,
            "email_cliente" => $customer['email'],
            "callback_url" => $callback_url,
            "razon_social" => $nit_name,
            "nit" => $nit_number,
        );
        if(isset($payment['customer_ci_number'])){
            $final_fields['ci'] = $payment['customer_ci_number'];
        } else if(isset($customer['ci_number'])){
            $final_fields['ci'] = $customer['ci_number'];
        }
        if(isset($payment['customer_ci_extension'])){
            $final_fields['ci_extension'] = $payment['customer_ci_extension'];
        } else if(isset($customer['ci_extension'])){
            $final_fields['ci_extension'] = $customer['ci_extension'];
        }
        if(isset($payment['customer_ci_expedition'])){
            $final_fields['ci_tipo'] = $payment['customer_ci_expedition'];
        } else if(isset($customer['ci_expedition'])){
            $final_fields['ci_tipo'] = $customer['ci_expedition'];
        }
        if(isset($customer['customer_code'])){
            $final_fields['codigo_cliente'] = $customer['customer_code'];
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
        if(isset($payment['currency'])){
            $final_fields['moneda'] = $payment['currency'];
        }
        if(isset($payment['invoice_type'])){
            $final_fields['factura_tipo'] = $payment['invoice_type'];
        }
        if(isset($payment['payment_type_code'])){
            $final_fields['metodo_pago'] = $payment['payment_type_code'];
        }
        if(isset($payment['commerce_user_code'])){
            $final_fields['usuario_comercio'] = $payment['commerce_user_code'];
        }
        if(isset($payment['card_number'])){
            $final_fields['numero_tarjeta'] = $payment['card_number'];
        }
        if(isset($payment['total_amount'])){
            $final_fields['monto_total'] = $payment['total_amount'];
        }/* else {
            $final_fields['monto_total'] = 0;
        }*/
        if(isset($payment['discount_amount'])){
            $final_fields['descuento_global'] = $payment['discount_amount'];
        }/* else {
            $final_fields['monto_descuento'] = 0;
        }*/
        if(isset($payment['shipping_amount'])){
            $final_fields['valor_envio'] = $payment['shipping_amount'];
        } else {
            $final_fields['valor_envio'] = 0;
        }
        if(isset($payment['shipping_detail'])){
            $shipping_detail = preg_replace('/[^A-Za-z0-9\-\(\) ]/', '', $payment['shipping_detail']); //removes ALL characters
            $final_fields['descripcion_envio'] = $shipping_detail;
        } else {
            $final_fields['descripcion_envio'] = "Costo de envío no detallado.";
        }
        $final_fields['descripcion'] = $payment['name'];
        if(isset($payment['preinvoices'])){
            $final_fields['prefacturas'] = $payment['preinvoices'];
        } else {
            $final_fields['lineas_detalle_deuda'] = $payment['items'];
        }
        // Habilitar Pago en Caja
        if(config('payments.pagostt_params.enable_cashier')&&isset($payment['canal_caja'])&&$payment['canal_caja']==true){
            $cashierKey = \Pagostt::getCashierKey($cashier_app_key, $custom_app_key);
            if($cashierKey&&isset($payment['canal_caja_sucursal'])&&isset($payment['canal_caja_usuario'])){
                $final_fields['canal_caja'] = $cashierKey;
                $final_fields['canal_caja_sucursal'] = $payment['canal_caja_sucursal'];
                $final_fields['canal_caja_usuario'] = $payment['canal_caja_usuario'];
            }
        }
        // Definir Metadata
        if(isset($payment['metadata'])){
            $final_fields['lineas_metadatos'] = $payment['metadata'];
        }
        if(config('payments.pagostt_params.enable_custom_func')){
            $final_fields = \CustomFunc::pagostt_params($final_fields, $customer, $payment, $transaction, $custom_app_key, $app_key, $cashier_app_key);
        }
        return $final_fields;
    }

    public static function generateTransactionQuery($transaction, $final_fields) {
        $url = \Pagostt::queryTransactiontUrl('deuda/registrar');
        \Log::info('pagostt_final_fields: '.json_encode($final_fields)); // OCULTAR
        $decoded_result = \Pagostt::queryCurlTransaction($url, $final_fields);
        if(!isset($decoded_result->url_pasarela_pagos)||!$decoded_result->url_pasarela_pagos){
            \Log::info('Iniciando Pago en Caja: '.json_encode($decoded_result));
            if($decoded_result->error==0&&isset($decoded_result->id_transaccion)){
                if(isset($decoded_result->facturas_electronicas)){
                    foreach($decoded_result->facturas_electronicas as $factura_electronica){
                        \Pagostt::putInoviceParametersCashier($transaction, $factura_electronica);
                        \Pagostt::putPaymentInvoice($transaction);
                    }
                }
                if(isset($decoded_result->datos)){
                    foreach($decoded_result->datos as $factura_electronica){
                        \Pagostt::putInoviceParametersCashier($transaction, $factura_electronica);
                        \Pagostt::putPaymentInvoice($transaction);
                    }
                }
                $transaction->external_payment_code = $decoded_result->id_transaccion;
                $transaction->status = 'paid';
                $transaction->save();
                $transaction->load('transaction_payments');
                if(config('payments.pagostt_params.enable_bridge')){
                    $payment_registered = \PagosttBridge::transactionSuccesful($transaction);
                } else {
                    $payment_registered = \Customer::transactionSuccesful($transaction);
                }
                \Log::info('Pago en Caja Generado: '.json_encode($payment_registered).' - '.json_encode($decoded_result));
                $transaction->load('processed_transaction_payments');
                if(count($transaction->processed_transaction_payments)>0){
                    foreach($transaction->processed_transaction_payments as $transaction_payment){
                        $payment = $transaction_payment->payment;
                        $payment->cashier_payment = true;
                        $payment->cashier_user_id = auth()->user()->id;
                        $payment->save();
                    }
                }
                return 'success-cashier';
            } else {
                \Log::info('Error en PagosTT Deuda: '.json_encode($decoded_result));
                return NULL;
            }
        } else {
            \Log::info('Success en PagosTT Deuda: '.json_encode($decoded_result));
        }

        // Guardado de transaction_id generado por PagosTT
        $transaction->callback_url = $decoded_result->url_pasarela_pagos;
        $transaction->external_payment_code = $decoded_result->id_transaccion;
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

    public static function queryTransactiontUrl($action) {
        if(config('payments.pagostt_params.testing')){
            $url = config('payments.pagostt_params.test_server');
        } else {
            $url = config('payments.pagostt_params.main_server');
        }
        $url .= $action;
        return $url;
    }

    public static function getAppKey($appkey = NULL, $custom_key = NULL) {
        if(!$appkey){
            if($custom_key){
                if(config('payments.pagostt_params.testing')==true&&config('payments.pagostt_params.custom_test_app_keys.'.$custom_key)){
                    $appkey = config('payments.pagostt_params.custom_test_app_keys.'.$custom_key);
                } else if(config('payments.pagostt_params.testing')==false&&config('payments.pagostt_params.custom_app_keys.'.$custom_key)) {
                    $appkey = config('payments.pagostt_params.custom_app_keys.'.$custom_key);
                }
            }
            if(!$appkey){
                if(config('payments.pagostt_params.testing')){
                    $appkey = config('payments.pagostt_params.test_app_key');
                } else {
                    $appkey = config('payments.pagostt_params.app_key');
                }
            }
        }
        return $appkey;
    }

    public static function getCashierKey($appkey = NULL, $custom_key = 'default') {
        if(!$appkey){
            if(config('payments.pagostt_params.testing')==true&&config('payments.pagostt_params.test_cashier_payments.'.$custom_key)){
                $appkey = config('payments.pagostt_params.test_cashier_payments.'.$custom_key);
            } else if(config('payments.pagostt_params.testing')==false&&config('payments.pagostt_params.cashier_payments.'.$custom_key)) {
                $appkey = config('payments.pagostt_params.cashier_payments.'.$custom_key);
            }
        }
        return $appkey;
    }

    public static function queryCurlTransaction($url, $final_fields) {
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
        \Log::info(json_encode($url));

        // Decodificar resultado
        $decoded_result = json_decode($result);
        return $decoded_result;
    }

    public static function generateTestingPayment() {
        $customer = ['email'=>'edumejia30@gmail.com','nit_name'=>'Mejia','nit_number'=>'4768578017','ci_number'=>'4768578','first_name'=>'Eduardo','first_name'=>'Eduardo','last_name'=>'Mejia'];
        $payment_lines = [\Pagostt::generatePaymentItem('Pago por muestra 1', 1, 100), \Pagostt::generatePaymentItem('Pago por muestra 2', 1, 100)];
        $payment = ['has_invoice'=>1,'name'=>'Pago de muestra 1','items'=>$payment_lines];
        $pagostt_transaction = \Pagostt::generatePaymentTransaction(1, [1], 200);
        $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_transaction);
        $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
        return $api_url;
    }

    public static function generatePreInvoices($payments_array, $appkey = NULL) {
        if(!config('payments.pagostt_params.enable_preinvoice')||count($payments_array)==0){
            return false;
        }
        $appkey = \Pagostt::getAppKey($appkey);
        $final_fields = [];
        $count = 0;
        $invoice_batch = time().'_'.rand(100000,900000);
        //\Log::info(json_encode($payments_array));
        foreach($payments_array as $payment_item){
            if(isset($payment_item['key_name'])){
                $key_name = $payment_item['key_name'];
            } else {
                $count++;
                $key_name = 'deuda_'.$count;
            }
            $final_fields[] = \Pagostt::generatePreInovicesItem($payment_item, $invoice_batch, $key_name, $appkey);
        }

        $url = \Pagostt::queryTransactiontUrl('prefacturas/registrar');
        \Log::info('Test Preinvoice en PagosTT: '.json_encode($final_fields));
        $decoded_result = \Pagostt::queryCurlTransaction($url, $final_fields);
        
        if(!$decoded_result||$decoded_result->error==1){
            \Log::info('Error en PagosTT Prefactura: '.json_encode($decoded_result->mensaje));
            return NULL;
        } else if($decoded_result->id_transaccion) {
            \Log::info('Success en PagosTT Prefactura: '.json_encode($decoded_result));
        }

        $correct_count = 0;
        $preinvoice_array = [];
        $preinvoice_errors = [];
        foreach($decoded_result->datos as $payment_response){
            if($preinvoice = \Solunes\Payments\App\Preinvoice::where('invoice_batch', $invoice_batch)->where('return_code', $payment_response->identificador_retorno)->first()){
                if($payment_response->error_generacion==false){
                    $correct_count++;
                    $transaction_id = $decoded_result->id_transaccion;
                    $preinvoice->pagostt_iterator = $payment_response->identificador_iteracion;
                    $preinvoice->pagostt_code = $payment_response->identificador_prefactura;
                    $preinvoice->pagostt_url = $payment_response->url;
                    $preinvoice_array[$payment_response->identificador_retorno] = ['code'=>$payment_response->identificador_prefactura,'url'=>$payment_response->url];
                } else {
                    $preinvoice->pagostt_error = 1;
                    $preinvoice->pagostt_message = $payment_response->mensaje;
                    $preinvoice_errors[$payment_response->identificador_retorno] = $payment_response->mensaje;
                }
                $preinvoice->save();
            } else {
                \Log::info('Preinvoice no encontrado luego de success');
            }
        }
        return ['success'=>true, 'invoice_batch'=>$invoice_batch, 'count'=>$correct_count, 'preinvoice_array'=>$preinvoice_array, 'preinvoice_errors'=>$preinvoice_errors];
    }

    public static function generatePreInovicesItem($payment_item, $invoice_batch, $key_name, $app_key) {
        $preinvoice = new \Solunes\Payments\App\Preinvoice;
        $preinvoice->payment_id = $payment_item['id'];
        $preinvoice->invoice_batch = $invoice_batch;
        $preinvoice->nit_name = $payment_item['nit_name'];
        $preinvoice->nit_number = $payment_item['nit_number'];
        $preinvoice->return_code = $key_name;
        $preinvoice->save();
        $final_fields = array(
            "appkey" => $app_key,
            "identificador_retorno" => $key_name,
            "razon_social" => $payment_item['nit_name'],
            "nit" => $payment_item['nit_number'],
            "lineas_detalle_factura" => $payment_item['detalle'],
        );
        foreach($payment_item['detalle'] as $detalle){
            $preinvoice_item = new \Solunes\Payments\App\PreinvoiceItem;
            $preinvoice_item->parent_id = $preinvoice->id;
            if(isset($detalle['detalle'])){
                $preinvoice_item->detail = $detalle['detalle'];
            } else {
                $preinvoice_item->name = $detalle['concepto'];
            }
            if(isset($detalle['codigo_producto'])){
                $preinvoice_item->product_code = $detalle['codigo_producto'];
            } else {
                $preinvoice_item->product_code = $payment_item['id'];
            }
            $preinvoice_item->quantity = $detalle['cantidad'];
            $preinvoice_item->price = $detalle['costo_unitario'];
            $preinvoice_item->amount = round(floatval($preinvoice_item->quantity) * floatval($preinvoice_item->price), 2);
            $preinvoice_item->save();
        }
        return $final_fields;
    }

    public static function checkInvoice($payment_code, $invoice_data) {
        $first_transaction = \Solunes\Payments\App\Transaction::where('payment_code',$payment_code)->first();
        $inserted = 0;
        if(!$first_transaction){
            return $inserted;
        }
        $first_transaction_payment = $first_transaction->transaction_payments()->first();
        if(!$first_transaction_payment){
            return $inserted;
        }
        $first_payment = \Solunes\Payments\App\Payment::find($first_transaction_payment->payment_id);
        if(!$first_payment){
            return $inserted;
        }
        \Log::info('Check payment: '.$invoice_data.' - '.$first_payment->invoice);
        $invoices_count = 0;
        foreach($first_transaction->transaction_payments as $transaction_payment){
            $payment = $transaction_payment->payment;
            if($payment->invoice){
                $invoices_count++;
            }
        }
        if($invoice_data&&$invoices_count>0){
            $inserted = 1;
        } else if(!$invoice_data&&$invoices_count==0) {
            $inserted = 1;
        }
        return $inserted;
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