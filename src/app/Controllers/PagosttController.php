<?php

namespace Solunes\Payments\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProcessController extends Controller {

	protected $request;
	protected $url;

	public function __construct(UrlGenerator $url) {
	  $this->prev = $url->previous();
	}

    public function getMakeAllPayments($customer_id) {
    	$customer = \PaymentsBridge::getCustomer($customer_id, true, false);
	    if($customer){
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
	      $payments_transaction = \Payments::generatePaymentTransaction($customer_id, $payment_ids, $total_amount);
	      $final_fields = \Payments::generateTransactionArray($customer, $payment, $payments_transaction);
	      $api_url = \Payments::generateTransactionQuery($payments_transaction, $final_fields);
	      if($api_url){
	      	return redirect($api_url);
	      } else {
	      	return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago en PagosTT.');
	      }
	    } else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error al realizar su pago.');
	    }
    }

    public function getMakeSinglePayment($customer_id, $payment_id) {
    	$customer = \PaymentsBridge::getCustomer($customer_id, false, false);
    	$payment = \PaymentsBridge::getPayment($payment_id);
	    if($customer&&$payment){
	      $payments_transaction = \Payments::generatePaymentTransaction($customer_id, [$payment_id], $payment['amount']);
	      $final_fields = \Payments::generateTransactionArray($customer, $payment, $payments_transaction);
	      $api_url = \Payments::generateTransactionQuery($payments_transaction, $final_fields);
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