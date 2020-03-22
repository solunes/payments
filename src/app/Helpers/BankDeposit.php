<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;

class BankDeposit {
    
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
          $transaction = \Payments::generatePaymentTransaction($payment->customer_id, [$payment->id], 'bank-deposit', $payment_object['amount']);
          if($transaction){
            return $transaction;
          } else {
            return NULL;
          }
        } else {
          \Log::info('Error, no hay Customer ('.json_encode($customer).') y Payment ('.json_encode($payment).')');
          return NULL;
        }
    }

}