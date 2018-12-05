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
	      $calc_array = \Payments::calculateMultiplePayments($customer['pending_payments']); // Returns items, payment_ids and amount.
	      $payment = $customer['payment'];
	      $payment['items'] = $calc_array['items'];
	      $payment = \Payments::getShippingCost($payment, $calc_array['payment_ids']);
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
	      $payment = \Payments::getShippingCost($payment, [$payment_id]);
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
  
    public function getMakeManualCashierPayment($customer_id, $payment_id, $custom_app_key = NULL) {
        if(config('payments.pagostt_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($customer_id, false, false, $custom_app_key);
    		$payment = \PagosttBridge::getPayment($payment_id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, false, false, $custom_app_key);
    		$payment = \Customer::getPayment($payment_id, $custom_app_key);
        }
        \Log::info('1: '.json_encode($customer).' - '.json_encode($payment));
	    if($customer&&$payment&&auth()->check()){
          \Log::info(2);
	      $payment = \Payments::getShippingCost($payment, [$payment_id]);
          $user = auth()->user();
          if($user->hasPermission('manual_payments')){
        	  \Log::info(3);
        	  if(config('pagostt.enable_bridge')){
		      	$cashier_data = \PagosttBridge::cashierPaymentData($user);
		  	  } else {
		      	$cashier_data = \Customer::cashierPaymentData($user);
		  	  }
		      $payment['canal_caja'] = true;
		      $payment['canal_caja_sucursal'] = $cashier_data['sucursal'];
		      $payment['canal_caja_usuario'] = $cashier_data['usuario'];
		      $pagostt_transaction = \Pagostt::generatePaymentTransaction($customer_id, [$payment_id], $payment['amount']);
		      $final_fields = \Pagostt::generateTransactionArray($customer, $payment, $pagostt_transaction, $custom_app_key);
		      $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
        	  \Log::info(4);
		      if($api_url){
        		\Log::info(5);
		      	if($api_url=='success-cashier'){
        		    \Log::info(6);
		      		return redirect($this->prev)->with('message_success', 'Su pago en caja fue procesado correctamente.');
		      	} else {
		      		return redirect($api_url);
		      	}
		      } else {
		      	return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
		      }
          } else {
		      return redirect($this->prev)->with('message_error', 'No tiene permisos para realizar un pago en caja.');
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
	      $calc_array = \Payments::calculateMultiplePayments($payments['pending_payments']); // Returns items, payment_ids and amount.
	      $payment = $payments['payment'];
	      $payment['items'] = $calc_array['items'];
	      $payment = \Payments::getShippingCost($payment, $calc_array['payment_ids']);
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