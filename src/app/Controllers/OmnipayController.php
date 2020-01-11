<?php 

namespace Solunes\Payments\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Paypalpayment;

class OmnipayPaymentController extends Controller {


    public function getMakeAllPayments($customer_id, $custom_app_key = NULL) {
        if(config('payments.paypal_params.enable_bridge')){
            $customer = \PaypalBridge::getCustomer($customer_id, true, false, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, true, false, $custom_app_key);
        }
        if($customer){
          $type = 'paypal';
          $total_amount = 0;
          $payment_ids = [];
          $items = [];
          foreach($customer['pending_payments'] as $payment_id => $pending_payment){
            $total_amount += $pending_payment['amount'];
            $payment_ids[] = $payment_id;
            foreach($pending_payment['items'] as $single_payment){
                $items[] = $single_payment;
            }
          }
          $payment = ['name'=>'Múltiples pagos', 'items'=>$items];
          $paypal_transaction = \OmnipayGateway::generatePaymentTransaction($customer_id, $payment_ids, $total_amount);
          $final_fields = \OmnipayGateway::generateTransactionArray($customer, $payment, $paypal_transaction, $type, $custom_app_key);
          $api_url = \OmnipayGateway::generateTransactionQuery($paypal_transaction, $final_fields, $type);
          if($api_url){
            return redirect($api_url);
          } else {
            return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
          }
        } else {
          return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
        }
    }

    public function getMakeSinglePayment($customer_id, $payment_id, $custom_app_key = NULL) {
        if(config('payments.paypal_params.enable_bridge')){
            $customer = \PaypalBridge::getCustomer($customer_id, false, false, $custom_app_key);
            $payment = \PaypalBridge::getPayment($payment_id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, false, false, $custom_app_key);
            $payment = \Customer::getPayment($payment_id, $custom_app_key);
        }
        if($customer&&$payment){
          $type = 'paypal';
          $paypal_transaction = \OmnipayGateway::generatePaymentTransaction($customer_id, [$payment_id], $payment['amount']);
          $final_fields = \OmnipayGateway::generateTransactionArray($customer, $payment, $paypal_transaction, $type, $custom_app_key);
          $api_url = \OmnipayGateway::generateTransactionQuery($paypal_transaction, $final_fields, $type);
          if($api_url){
            return redirect($api_url);
          } else {
            return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
          }
        } else {
          return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
        }
    }

    public function postMakeCheckboxPayment(Request $request) {
        $company = $request->input('company');
        $payments_array = $request->input('check_'.$company);
        if(config('payments.paypal_params.enable_bridge')){
            $customer = \PaypalBridge::getCustomer($customer_id, true, false, $custom_app_key);
            $payments = \PaypalBridge::getCheckboxPayments($customer_id, $payments_array, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, true, false, $custom_app_key);
            $payments = \Customer::getCheckboxPayments($customer_id, $payments_array, $custom_app_key);
        }
        if($customer&&count($payments)>0){
          $type = 'paypal';
          $total_amount = 0;
          $payment = ['name'=>'Múltiples pagos seleccionados', 'items'=>$payments];
          $paypal_transaction = \OmnipayGateway::generatePaymentTransaction($customer_id, $payment_ids, $total_amount);
          $final_fields = \OmnipayGateway::generateTransactionArray($customer, $payment, $paypal_transaction, $type, $custom_app_key);
          $api_url = \OmnipayGateway::generateTransactionQuery($paypal_transaction, $final_fields, $type);
          if($api_url){
            return redirect($api_url);
          } else {
            return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
          }
        } else {
          return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
        }
    }

}