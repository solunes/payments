<?php

namespace Solunes\Payments\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class PaymeController extends Controller {

	protected $request;
	protected $url;

	public function __construct(UrlGenerator $url) {
	  $this->prev = $url->previous();
	}

    public function getPaymentIframe($payment_code) {
    	$array = \Payme::generatePaymentArray($payment_code);
    	$array['page'] = \Solunes\Master\App\Page::first();
	    return view('payments::payme.purchase', $array);
    }

    public function getTransactionFromPayme($payment_code) {
    	$array['response'] = \Payme::getTransactionFromPayme($payment_code);
    	$array['page'] = \Solunes\Master\App\Page::first();
	    return $array['response'];
    }

    public function getTransactionFromPayme($payment_code) {
	    return view('payments::payme.successful-purchase', $array);
    }

    public function postSuccessfulPayment(Request $request) {
    	\Log::info('postSuccessfulPayment: '.json_encode($request->all()));
    	$payment_code = $request->get('reserved1');
    	$purchaseVerification = $request->get('purchaseVerification');
    	$successfulPayment = \Payme::successfulPayment($payment_code, $purchaseVerification);
    	if($successfulPayment){
	      return redirect('admin/transaction-from-payme/'.$payment_code)->with('message_success', 'Su pago fue recibido correctamente.');
	    } else {
	      return redirect('admin/transaction-from-payme/'.$payment_code)->with('message_error', 'Hubo un error al realizar su pago.');
	    }
    }

    public function getMakeAllPayments($customer_id, $custom_app_key = NULL) {
        if(config('payments.payme_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($customer_id, true, false, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, true, false, $custom_app_key);
        }
	    if($customer){
	      $calc_array = \Payments::calculateMultiplePayments($customer['pending_payments']); // Returns items, payment_ids and amount.
	      $payment = $customer['payment'];
	      $payment['items'] = $calc_array['items'];
	      $transaction = \Payments::generatePaymentTransaction($customer_id, $calc_array['payment_ids'], 'payme');
	      $api_url = \Payme::generatePaymentUrl($transaction);
	      if($api_url){
	      	return redirect($api_url)->with('message_success', 'Confirme su pago por favor.');
	      } else {
	      	return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en Payme.');
	      }
	    } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
	    }
    }
  
    public function getMakeSinglePayment($customer_id, $payment_id, $custom_app_key = NULL) {
        if(config('payments.payme_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($customer_id, false, false, $custom_app_key);
    		$payment = \PagosttBridge::getPayment($payment_id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, false, false, $custom_app_key);
    		$payment = \Customer::getPayment($payment_id, $custom_app_key);
        }
	    if($customer&&$payment){
	      $transaction = \Payments::generatePaymentTransaction($customer_id, [$payment_id], 'payme');
	      $api_url = \Payme::generatePaymentUrl($transaction);
	      if($api_url){
	      	return redirect($api_url)->with('message_success', 'Confirme su pago por favor.');
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
        if(config('payments.payme_params.enable_bridge')){
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
	      $transaction = \Payments::generatePaymentTransaction($customer_id, $calc_array['payment_ids'], 'payme');
	      $api_url = \Payme::generatePaymentUrl($transaction);
	      if($api_url){
	      	return redirect($api_url)->with('message_success', 'Confirme su pago por favor.');
	      } else {
	      	return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
	      }
        } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
        }
    }

}