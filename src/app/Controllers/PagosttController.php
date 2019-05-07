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

    public function getCreateCustomPayment($customer_id) {
    	$user = auth()->user();
    	/*if(!$user->hasRole('admin')){
	    	return redirect($this->prev)->with('message_error', 'Debe ser un adminisrador para ingresar.');
    	}*/
    	$customer = \Solunes\Customer\App\Customer::find($customer_id);
    	if(!$customer){
	    	return redirect($this->prev)->with('message_error', 'No se encuentra un cliente asociado.');
    	}
    	$array['customer'] = $customer;
        return view('payments::item.custom-payment', $array);
    }

    public function postCreateCustomPayment(Request $request) {
    	$user = auth()->user();
    	$customer = \Solunes\Customer\App\Customer::find($request->input('customer_id'));
        $subprice = $request->input('price') - $request->input('discount_price');
        $category = \Solunes\Product\App\Category::first();
        if(!$category){
            $category = new \Solunes\Product\App\Category;
            $category->level = 1;
            $category->name = 'General';
            $category->save();
        }
        $product = new \Solunes\Product\App\Product;
        $product->category_id = $category->id;
        $product->currency_id = 1;
        $product->name = $request->input('name');
        $product->price = $request->input('price');
        if(config('payments.sfv_version')>1){
            $product->discount_price = $subprice;
            $product->economic_sin_activity = $request->input('economic_sin_activity');
            $product->product_sin_code = $request->input('product_sin_code');
            $product->product_internal_code = $request->input('product_internal_code');
            $product->product_serial_number = $request->input('product_serial_number');
        }
        $product->active = 1;
        $product->save();
        $product_bridge = \Solunes\Business\App\ProductBridge::where('product_type', 'product')->where('product_id', $product->id)->first();
    	$sale = new \Solunes\Sales\App\Sale;
    	$sale->user_id = $user->id;
    	$sale->customer_id = $customer->id;
    	$sale->agency_id = 1;
    	$sale->currency_id = 1;
        $sale->name = $request->input('name');
        $sale->amount = $subprice * $request->input('quantity');
    	$sale->invoice = 1;
    	$sale->invoice_name = $request->input('invoice_name');
    	$sale->invoice_nit = $request->input('invoice_nit');
    	$sale->save();
    	$sale_item = new \Solunes\Sales\App\SaleItem;
    	$sale_item->parent_id = $sale->id;
    	$sale_item->product_bridge_id = $product_bridge->id;
    	$sale_item->currency_id = $sale->currency_id;
        if(config('sales.delivery')){
            $sale_item->weight = 0;
        }
        $sale_item->price = $subprice;
        if(config('payments.sfv_version')>1||config('payments.discounts')){
    	   $sale_item->discount_price = $request->input('discount_price');
            $sale_item->discount_amount = $request->input('discount_price') * $request->input('quantity');
        }
    	$sale_item->quantity = $request->input('quantity');
    	$sale_item->total = $subprice * $request->input('quantity');
        if(config('payments.sfv_version')>1){
        	$sale_item->economic_sin_activity = $request->input('economic_sin_activity');
        	$sale_item->product_sin_code = $request->input('product_sin_code');
        	$sale_item->product_internal_code = $request->input('product_internal_code');
        	$sale_item->product_serial_number = $request->input('product_serial_number');
        }
    	$sale_item->save();
    	$sale_payment = new \Solunes\Sales\App\SalePayment;
    	$sale_payment->parent_id = $sale->id;
    	$sale_payment->currency_id = $sale->currency_id;
    	$sale_payment->payment_method_id = 3; // PAGOSTT
    	$sale_payment->amount = $sale_item->total;
        if(config('payments.sfv_version')>1||config('payments.discounts')){
    	   $sale_payment->discount_amount = $sale_item->discount_amount;
        }
        if(config('sales.delivery')){
    	   $sale_payment->pay_delivery = 1;
        }
    	$sale_payment->commerce_user_code = $request->input('commerce_user_code');
    	$sale_payment->customer_code = $request->input('customer_code');
    	$sale_payment->customer_ci_number = $request->input('customer_ci_number');
    	$sale_payment->customer_ci_extension = $request->input('customer_ci_extension');
    	$sale_payment->customer_ci_expedition = $request->input('customer_ci_expedition');
        if(config('payments.sfv_version')>1){
            $sale_payment->invoice_type = $request->input('invoice_type');
            $sale_payment->payment_type_code = $request->input('payment_type_code');
            $sale_payment->card_number = $request->input('card_number');
        }
    	$sale_payment->save();
    	$payment = \Payments::generatePayment($sale);
    	$custom_app_key = 'default';
        if(config('payments.pagostt_params.enable_bridge')){
            $customer_object = \PagosttBridge::getCustomer($customer->id, false, false, $custom_app_key);
    		$payment_object = \PagosttBridge::getPayment($payment->id, $custom_app_key);
        } else {
            $customer_object = \Customer::getCustomer($customer->id, false, false, $custom_app_key);
    		$payment_object = \Customer::getPayment($payment->id, $custom_app_key);
        }
	    if($customer_object&&$payment_object){
	      $payment_object = \Payments::getShippingCost($payment_object, [$payment->id]);
	      $pagostt_transaction = \Pagostt::generatePaymentTransaction($customer->id, [$payment->id], $payment_object['amount']);
	      $final_fields = \Pagostt::generateTransactionArray($customer_object, $payment_object, $pagostt_transaction, $custom_app_key);
	      $api_url = \Pagostt::generateTransactionQuery($pagostt_transaction, $final_fields);
	      if($api_url){
	      	if($api_url=='success-cashier'){
	      		return redirect($this->prev)->with('message_success', 'Su pago en caja fue procesado correctamente.');
	      	} else {
	      		return redirect($api_url);
	      	}
	      }
    	} else {
	      return redirect($this->prev)->with('message_error', 'Hubo un error.');
    	}
    }

    public function getMakeAllPayments($customer_id, $custom_app_key = 'default') {
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
  
    public function getMakeSinglePayment($customer_id, $payment_id, $custom_app_key = 'default') {
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
  
    public function getMakeManualCashierPayment($customer_id, $payment_id, $custom_app_key = 'default') {
        if(config('payments.pagostt_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($customer_id, false, false, $custom_app_key);
    		$payment = \PagosttBridge::getPayment($payment_id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($customer_id, false, false, $custom_app_key);
    		$payment = \Customer::getPayment($payment_id, $custom_app_key);
        }
	    if($customer&&$payment&&auth()->check()){
	      $payment = \Payments::getShippingCost($payment, [$payment_id]);
          $user = auth()->user();
          if($user->hasPermission('manual_payments')){
        	  if(config('payments.pagostt_params.enable_bridge')){
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
		      if($api_url){
		      	if($api_url=='success-cashier'){
                    $payment = \Solunes\Payments\App\Payment::find($payment_id);
                    if($payment){
                        $payment->cashier_payment = true;
                        $payment->cashier_user_id = $user->id;
                        $payment->save();
                    }
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