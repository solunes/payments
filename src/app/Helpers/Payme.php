<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Payme {

    public static function generateSalePayment($payment_item, $cancel_url) {
        $custom_app_key = NULL;
        if(config('payments.payme_params.enable_bridge')){
            $customer = \PaymeBridge::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \PaymeBridge::getPayment($payment_item->id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \Customer::getPayment($payment_item->id, $custom_app_key);
        }
        if($customer&&$payment){
          $pagostt_transaction = \Payme::generatePaymentTransaction($payment_item->customer_id, [$payment_item->id], $payment['amount']);
          $final_fields = \Payme::generateTransactionArray($customer, $payment, $pagostt_transaction, $custom_app_key);
          $api_url = \Payme::generateTransactionQuery($pagostt_transaction, $final_fields);
          if($api_url){
            return $api_url;
          } else {
            return NULL;
          }
        } else {
          return NULL;
        }
    }

    public static function generateOperationNumber() {
        $token = \Payme::generateToken();
        if(\Solunes\Payments\App\Transaction::where('external_payment_code', $token)->first()){
            $token = \Payme::generateOperationNumber();
        }
        return $token;
    }

    public static function generateToken() {
        $time = time();
        $full_token = substr($time, 4, 9).rand(100,999);
        return $full_token;
    }

    public static function generatePaymentArray($payment_code) {
        $transaction = \Solunes\Payments\App\Transaction::where('payment_code', $payment_code)->where('status', 'holding')->first();
        if(config('payments.payme_params.testing')===false){
            $url = config('payments.payme_params.main_server');
            $claveSecreta = config('payments.payme_params.sha_key_production');
            $acquirerId = config('payments.payme_params.acquirer_id_production');
            $idCommerce = config('payments.payme_params.commerce_id_production');
        } else {
            $url = config('payments.payme_params.test_server');
            $claveSecreta = config('payments.payme_params.sha_key_testing');
            $acquirerId = config('payments.payme_params.acquirer_id_testing');
            $idCommerce = config('payments.payme_params.commerce_id_testing');
        }
        $purchaseOperationNumber = $transaction->external_payment_code;
        $purchaseAmount = 0;
        foreach($transaction->transaction_payments as $transaction_payment){
            $purchaseAmount += $transaction_payment->payment->amount;
        }
        $purchaseCurrencyCode = config('payments.payme_params.iso_currency_code');
        $purchaseVerification = openssl_digest($acquirerId . $idCommerce . $purchaseOperationNumber . $purchaseAmount . $purchaseCurrencyCode . $claveSecreta, 'sha512');
        return ['url'=>$url, 'acquirerId'=>$acquirerId, 'idCommerce'=>$idCommerce, 'purchaseOperationNumber'=>$purchaseOperationNumber, 'purchaseAmount'=>$purchaseAmount, 'purchaseCurrencyCode'=>$purchaseCurrencyCode, 'purchaseVerification'=>$purchaseVerification];
    }

    public static function generatePaymentUrl($transaction) {
        $url = url('payme/payment-iframe/'.$transaction->payment_code);
        \Log::info('Success en Payme Deuda: '.json_encode($transaction));

        // Guardado de transaction_id generado por PagosTT
        $transaction->callback_url = $url;
        $transaction->external_payment_code = \Payme::generateOperationNumber();
        $transaction->save();
        
        // URL para redireccionar
        return $url;
    }

    public static function generateTransactionQuery($transaction, $final_fields) {
        $url = url('payme/payment-iframe/'.$transaction->payment_code);
        $decoded_result = \Payme::queryCurlTransaction($url, $final_fields);
        \Log::info('Success en Payme Deuda: '.json_encode($decoded_result));

        // Guardado de transaction_id generado por PagosTT
        $transaction->external_payment_code = \Payme::generateOperationNumber();
        $transaction->save();
        
        // URL para redireccionar
        return $url;
    }

    public static function queryTransactiontUrl($action) {
        if(config('payments.payme_params.testing')){
            $url = config('payments.payme_params.test_server');
        } else {
            $url = config('payments.payme_params.main_server');
        }
        $url .= $action;
        return $url;
    }

    public static function getShaKey($purchaseOperationNumber) {
        if(config('payments.payme_params.testing')===false){
            $claveSecreta = config('payments.payme_params.sha_key_testing');
            $acquirerId = config('payments.payme_params.acquirer_id_testing');
            $idCommerce = config('payments.payme_params.commerce_id_testing');
        } else {
            $claveSecreta = config('payments.payme_params.sha_key_production');
            $acquirerId = config('payments.payme_params.acquirer_id_production');
            $idCommerce = config('payments.payme_params.commerce_id_production');
        }
        $purchaseVerification = openssl_digest($acquirerId . $idCommerce . $purchaseOperationNumber . $claveSecreta, 'sha512');
        return $purchaseVerification;
    }

    public static function queryCurlTransaction($url, $final_fields) {
        $header = array('Content-Type: application/json');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POSTFIELDS, $final_fields);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        \Log::info(json_encode($url));

        return $code;
    }

    public static function generateTestingPayment() {
        $customer = ['email'=>'edumejia30@gmail.com','nit_name'=>'Mejia','nit_number'=>'4768578017','ci_number'=>'4768578','first_name'=>'Eduardo','first_name'=>'Eduardo','last_name'=>'Mejia'];
        $payment_lines = [\Payme::generatePaymentItem('Pago por muestra 1', 1, 100), \Payme::generatePaymentItem('Pago por muestra 2', 1, 100)];
        $payment = ['has_invoice'=>1,'name'=>'Pago de muestra 1','items'=>$payment_lines];
        $pagostt_transaction = \Payme::generatePaymentTransaction(1, [1], 200);
        $final_fields = \Payme::generateTransactionArray($customer, $payment, $pagostt_transaction);
        $api_url = \Payme::generateTransactionQuery($pagostt_transaction, $final_fields);
        return $api_url;
    }

}