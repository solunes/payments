<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class TestPayment {
    
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
          $transaction = \Payments::generatePaymentTransaction($payment->customer_id, [$payment->id], 'test-payment', $payment_object['amount']);
          $api_url = \Payments::generatePaymentCallback($transaction->payment_code);
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

}