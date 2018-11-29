<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Payme {

    public static function generateSalePayment($payment_item, $cancel_url) {
        $custom_app_key = NULL;
        if(config('payments.payme_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \PagosttBridge::getPayment($payment_item->id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \Customer::getPayment($payment_item->id, $custom_app_key);
        }
        if($customer&&$payment){
          $transaction = \Payments::generatePaymentTransaction($payment_item->customer_id, [$payment_item->id], 'payme');
          $api_url = \Payme::generatePaymentUrl($transaction);
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

    public static function generateWalletAccount($codCardHolderCommerce, $mail, $first_name, $last_name) {
        if(config('payments.payme_params.testing')===false){
            $url = 'https://www.pay-me.pe/';
            $claveSecreta = config('payments.payme_params.shawallet_key_production');
            $idEntCommerce = config('payments.payme_params.idEntCommerce_production');
        } else {
            $url = config('payments.payme_params.test_server');
            $claveSecreta = config('payments.payme_params.shawallet_key_testing');
            $idEntCommerce = config('payments.payme_params.idEntCommerce_testing');
        }
        $url .= 'WALLETWS/services/WalletCommerce?wsdl';
        \Log::info('generateWalletAccount 1: '.$idEntCommerce.' - '.$codCardHolderCommerce.' - '.$mail.' - '.$claveSecreta);
        $registerVerification = openssl_digest($idEntCommerce . $codCardHolderCommerce . $mail . $claveSecreta, 'sha512');

        //Referencia al Servicio Web de Wallet            
        $client = new \SoapClient($url);

        //Creación de Arreglo para el almacenamiento y envío de parametros. 
        $params = array(
            'idEntCommerce'=>$idEntCommerce,
            'codCardHolderCommerce'=>$codCardHolderCommerce,
            'names'=>$first_name,
            'lastNames'=>$last_name,
            'mail'=>$mail,
            'registerVerification'=>$registerVerification
        );
        
        //Consumo del metodo RegisterCardHolder
        $result = $client->RegisterCardHolder($params);
        \Log::info('generateWalletAccount 2: '.$result->ansCode.' - '.$result->ansDescription.' - '.$result->codAsoCardHolderWallet.' - '.$result->date.' - '.$result->hour);
        return ['codCardHolderCommerce'=>$codCardHolderCommerce,'codAsoCardHolderWallet'=>$result->codAsoCardHolderWallet];
    }

    public static function generatePaymentArray($payment_code) {
        $transaction = \Solunes\Payments\App\Transaction::where('payment_code', $payment_code)->where('status', 'holding')->first();
        $customer = $transaction->customer;
        $userCommerce = NULL;
        $userCodePayme = NULL;
        if($customer){
            $walletAccount = \Payme::generateWalletAccount($customer->id, $customer->email, $customer->first_name, $customer->last_name);
            $userCommerce = $walletAccount['codCardHolderCommerce'];
            $userCodePayme = $walletAccount['codAsoCardHolderWallet'];
        }
        if(config('payments.payme_params.testing')===false){
            $url = config('payments.payme_params.main_server');
            $claveSecreta = config('payments.payme_params.sha_key_production');
            $acquirerId = config('payments.payme_params.acquirer_id_production');
            $idCommerce = config('payments.payme_params.commerce_id_production');
            $model_url = "'', '".config('payments.payme_params.design_option')."'";
        } else {
            $url = config('payments.payme_params.test_server');
            $claveSecreta = config('payments.payme_params.sha_key_testing');
            $acquirerId = config('payments.payme_params.acquirer_id_testing');
            $idCommerce = config('payments.payme_params.commerce_id_testing');
            $model_url = "'https://integracion.alignetsac.com/'";
        }
        $url .= 'VPOS2/';
        $purchaseOperationNumber = $transaction->external_payment_code;
        $purchaseOperationNumber = rand(100000000,999999999); // BORRAR
        if(config('payments.payme_params.min_amount')){
            $purchaseAmount = '1.00';
        } else {
            $purchaseAmount = 0;
            foreach($transaction->transaction_payments as $transaction_payment){
                $purchaseAmount += $transaction_payment->payment->amount;
            }
        }
        $purchaseAmount = (string) $purchaseAmount;
        $purchaseAmount = str_replace('.', '', $purchaseAmount);
        $purchaseCurrencyCode = config('payments.payme_params.iso_currency_code');
        $purchaseVerification = openssl_digest($acquirerId . $idCommerce . $purchaseOperationNumber . $purchaseAmount . $purchaseCurrencyCode . $claveSecreta, 'sha512');
        return ['url'=>$url, 'model_url'=>$model_url, 'acquirerId'=>$acquirerId, 'acquirerId'=>$acquirerId, 'acquirerId'=>$acquirerId, 'idCommerce'=>$idCommerce, 'purchaseOperationNumber'=>$purchaseOperationNumber, 'purchaseAmount'=>$purchaseAmount, 'purchaseCurrencyCode'=>$purchaseCurrencyCode, 'purchaseVerification'=>$purchaseVerification, 'userCommerce'=>$userCommerce, 'userCodePayme'=>$userCodePayme];
    }

    public static function successfulPayment($payment_code, $purchaseVerificationRecieved) {
        $transaction = \Solunes\Payments\App\Transaction::where('payment_code', $payment_code)->first();
        if(config('payments.payme_params.testing')===false){
            $url = config('payments.payme_params.main_server');
            $claveSecreta = config('payments.payme_params.sha_key_production');
            $acquirerId = config('payments.payme_params.acquirer_id_production');
            $idCommerce = config('payments.payme_params.commerce_id_production');
            $model_url = "'', '".config('payments.payme_params.design_option')."'";
        } else {
            $url = config('payments.payme_params.test_server');
            $claveSecreta = config('payments.payme_params.sha_key_testing');
            $acquirerId = config('payments.payme_params.acquirer_id_testing');
            $idCommerce = config('payments.payme_params.commerce_id_testing');
            $model_url = "'https://integracion.alignetsac.com/'";
        }
        $purchaseOperationNumber = $transaction->external_payment_code;
        \Log::info('successfulPayment 1: '.$acquirerId.' - '.$idCommerce.' - '.$purchaseOperationNumber.' - '.$claveSecreta);
        $purchaseVerification = openssl_digest($acquirerId . $idCommerce . $purchaseOperationNumber . $claveSecreta, 'sha512');
        \Log::info('successfulPayment 2: '.$purchaseVerification);
        if($purchaseVerificationRecieved==$purchaseVerification){
            return true;
        }
        return false;
    }

    public static function getTransactionFromPayme($payment_code) {
        $transaction = \Solunes\Payments\App\Transaction::where('payment_code', $payment_code)->first();
        if(config('payments.payme_params.testing')===false){
            $url = config('payments.payme_params.main_server');
            $claveSecreta = config('payments.payme_params.sha_key_production');
            $acquirerId = config('payments.payme_params.acquirer_id_production');
            $idCommerce = config('payments.payme_params.commerce_id_production');
            $model_url = "'', '".config('payments.payme_params.design_option')."'";
        } else {
            $url = config('payments.payme_params.test_server');
            $claveSecreta = config('payments.payme_params.sha_key_testing');
            $acquirerId = config('payments.payme_params.acquirer_id_testing');
            $idCommerce = config('payments.payme_params.commerce_id_testing');
            $model_url = "'https://integracion.alignetsac.com/'";
        }
        $url .= 'VPOS2/rest/operationAcquirer/consulte';
        $purchaseOperationNumber = $transaction->external_payment_code;
        \Log::info($acquirerId.' - '.$idCommerce.' - '.$purchaseOperationNumber.' - '.$claveSecreta);
        $purchaseVerification = openssl_digest($acquirerId . $idCommerce . $purchaseOperationNumber . $claveSecreta, 'sha512');
        \Log::info($purchaseVerification);
        $dataRest = '{"idAcquirer":"'.$acquirerId.'","idCommerce":"'.$idCommerce.'","operationNumber":"'.$purchaseOperationNumber.'","purchaseVerification":"'.$purchaseVerification.'"}';
        $header = array('Content-Type: application/json');
        
        //Consumo del servicio Rest
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POSTFIELDS, $dataRest);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        //Imprimir respuesta
        \Log::info('Response api get transaction: '.json_encode($response));
        return $response;
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