<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class Payu {

    public static function generateSalePayment($payment_item, $cancel_url) {
        $custom_app_key = NULL;
        if(config('payments.payu_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \PagosttBridge::getPayment($payment_item->id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($payment_item->customer_id, false, false, $custom_app_key);
            $payment = \Customer::getPayment($payment_item->id, $custom_app_key);
        }
        if($customer&&$payment){
          $transaction = \Payments::generatePaymentTransaction($payment_item->customer_id, [$payment_item->id], 'payu');
          $api_url = \Payu::generatePaymentUrl($transaction);
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

    public static function generatePaymentUrl($transaction) {
        $url = 'https://sandbox.checkout.payulatam.com';
        $action = 'ppp-web-gateway-payu';
        $parameters = [];
        $parameters['merchantId'] = '508029';
        $parameters['accountId'] = '512321';
        $parameters['description'] = 'Test PAYU';
        $parameters['referenceCode'] = 'TestPayU';
        $parameters['amount'] = '20000';
        $parameters['tax'] = '3193';
        $parameters['taxReturnBase'] = '16806';
        $parameters['currency'] = 'COP';
        $parameters['signature'] = '7ee7cf808ce6a39b17481c54f2c57acc';
        $parameters['test'] = '1';
        $parameters['buyerEmail'] = 'test@test.com';
        $parameters['responseUrl'] = 'http://www.test.com/response';
        $parameters['confirmationUrl'] = 'http://www.test.com/confirmatio';

$client = new \GuzzleHttp\Client();

$client->post($url.'/'.$action, [
    'query'   => ['data' => $parameters],
    'on_stats' => function (\GuzzleHttp\TransferStats $stats) use (&$url) {
        $url = $stats->getEffectiveUri();
    }
])->getBody()->getContents();
\Log::info('url: '.$url);
return $url; // http://some.site.com?get=params

        $post = \External::guzzlePost($url, $action, $parameters);

        return $post;
        \Log::info('post: '.json_encode($post));

        // Guardado de transaction_id generado por PagosTT
        /*$transaction->callback_url = $url;
        $transaction->external_payment_code = \Payme::generateOperationNumber();
        $transaction->save();*/
        
        // URL para redireccionar
        return $url;
    }

}