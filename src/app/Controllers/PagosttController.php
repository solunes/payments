<?php

namespace Solunes\Payments\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class PagosttController extends Controller {

	protected $request;
	protected $url;

	public function __construct(UrlGenerator $url) {
	  $this->prev = $url->previous();
	}

    public function getMakeAllPayments($customer_id, $custom_app_key = NULL) {
        if(config('payments.pagostt_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($customer_id, true, false, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, true, false, $custom_app_key);
        }
	    if($customer){
	      $calc_array = \Pagostt::calculateMultiplePayments($customer['pending_payments']); // Returns items, payment_ids and amount.
	      $payment = $customer['payment'];
	      $payment['items'] = $calc_array['items'];
	      $pagostt_transaction = \Pagostt::generatePaymentTransaction($customer_id, $calc_array['payment_ids'], $calc_array['total_amount']);
	      $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_transaction, $custom_app_key);
	      $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
	      if($api_url){
	      	if($api_url=='success-cashier'){
	      		return redirect($this->prev)->with('message_success', 'Su pago en caja fue procesado correctamente.');
	      	} else {
	      		return redirect($api_url);
	      	}
	      } else {
	      	return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
	      }
	    } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
	    }
    }
  
    public function getMakeSinglePayment($customer_id, $payment_id, $custom_app_key = NULL) {
        if(config('payments.pagostt_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($customer_id, false, false, $custom_app_key);
    		$payment = \PagosttBridge::getPayment($payment_id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, false, false, $custom_app_key);
    		$payment = \Customer::getPayment($payment_id, $custom_app_key);
        }
	    if($customer&&$payment){
	      $pagostt_transaction = \Pagostt::generatePaymentTransaction($customer_id, [$payment_id], $payment['amount']);
	      $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_transaction, $custom_app_key);
	      $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
	      if($api_url){
	      	if($api_url=='success-cashier'){
	      		return redirect($this->prev)->with('message_success', 'Su pago en caja fue procesado correctamente.');
	      	} else {
	      		return redirect($api_url);
	      	}
	      } else {
	      	return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
	      }
	    } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
	    }
    }

    public function postMakeCheckboxPayment(Request $request) {
        $custom_app_key = $request->input('custom_app_key');
        $customer_id = $request->input('customer_id');
        $payments_array = $request->input('check');
        if(config('payments.pagostt_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($customer_id, false, false, $custom_app_key);
            $payments = \PagosttBridge::getCheckboxPayments($customer_id, $payments_array, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, false, false, $custom_app_key);
            $payments = \Customer::getCheckboxPayments($customer_id, $payments_array, $custom_app_key);
        }
	    if($customer&&count($payments)>0){
	      $calc_array = \Pagostt::calculateMultiplePayments($payments['pending_payments']); // Returns items, payment_ids and amount.
	      $payment = $payments['payment'];
	      $payment['items'] = $calc_array['items'];
	      $pagostt_transaction = \Pagostt::generatePaymentTransaction($customer_id, $calc_array['payment_ids'], $calc_array['total_amount']);
	      $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_transaction, $custom_app_key);
	      $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
	      if($api_url){
	      	if($api_url=='success-cashier'){
	      		return redirect($this->prev)->with('message_success', 'Su pago en caja fue procesado correctamente.');
	      	} else {
	      		return redirect($api_url);
	      	}
	      } else {
	      	return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
	      }
        } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
        }
    }

}