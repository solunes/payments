<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class OmnipayGateway {
    
    public static function generateSalePayment($payment, $cancel_url) {
        $custom_app_key = NULL;
        if(config('payments.pagostt_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($payment->customer_id, false, false, $custom_app_key);
            $payment_object = \PagosttBridge::getPayment($payment->id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($payment->customer_id, false, false, $custom_app_key);
            $payment_object = \Customer::getPayment($payment->id, $custom_app_key);
        }
        if($customer&&$payment){
          $payment_object = \Payments::getShippingCost($payment_object, [$payment->id]);
          $payments_transaction = \Payments::generatePaymentTransaction($payment->customer_id, [$payment->id], 'omnipay', $payment_object['amount']);
          $parameters = \OmnipayGateway::generateTransactionArray($customer, $payment, $payments_transaction, $custom_app_key);
          $api_url = \OmnipayGateway::generateTransactionQuery($payments_transaction, $parameters);
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

    public static function generateTransactionArray($customer, $payment, $payments_transaction, $custom_app_key){
        $items = [];
        $amount = 0;
        $payment->load('payment_items');
        foreach($payment->payment_items as $payment_item){
            $amount += $payment_item->amount;
            $items[] = ['name'=>$payment_item->name,'description'=>$payment_item->detail,'unit_amount'=>['currency_code'=>'USD','value'=>$payment_item->price],'quantity'=>$payment_item->quantity,'item_total'=>$payment_item->amount,'category'=>'PHYSICAL_GOODS'];
        }
        $parameters = ['reference_id'=>'PAY-'.$payment->id,'amount'=>$amount,'currency'=>'BOB','items'=>$items];
        return $parameters;
    }

    public static function generateTransactionQuery($transaction, $parameters){
        $url = \OmnipayGateway::getUrl();
        $response = \Omnipay::purchase($parameters)->send();
        if ($response->isSuccessful()) {
            // payment was successful: update database
            print_r($response);
            return $redirectHomeSuccess;
        } else if ($response->isRedirect()) {
            // redirect to offsite payment gateway
            return $response->getRedirectUrl();
        } else {
            // payment failed: display message to customer
            return false;
        }
    }

    public static function getUrl(){
        $sandboxUrl = 'https://api.sandbox.paypal.com';
        $liveUrl = 'https://api.paypal.com';
        if(config('payments.omnipay_params.testing')){
            return $sandboxUrl;
        } else {
            return $liveUrl;
        }
    }

    public static function getToken(){
        $url = \Paypal::getUrl();
        if(config('payments.omnipay_params.testing')){
            $client_id = config('payments.paypal_params.sandbox_api_client');
            $secret = config('payments.paypal_params.sandbox_api_secret');
        } else {
            $client_id = config('payments.paypal_params.live_api_client');
            $secret = config('payments.paypal_params.live_api_secret');
        }
        $headers = ['auth_username'=>$client_id, 'auth_password'=>$secret, 'Content-Type'=>'application/x-www-form-urlencoded', 'Accept'=>'application/json', 'Accept-Language'=>'en_US'];
        $response = \External::guzzlePost($url, 'oauth2/token', ['grant_type'=>'client_credentials'], $headers);
        if(isset($response['access_token'])){
            \Log::info('Acccess Token Response: '.json_encode($response['access_token']));
            return $response['access_token'];
        }
        return $response;
    }

    public static function getAccessToken(){
        if(config('payments.paypal_params.testing')){
            $access_token = config('payments.omnipay_params.sandbox_access_token');
        } else {
            $access_token = config('payments.omnipay_params.live_access_token');
        }
        if(!$access_token){
            $access_token = \Paypal::getToken();
        }
        return $access_token;
    }

}