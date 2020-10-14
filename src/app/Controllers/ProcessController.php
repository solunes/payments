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
    //$this->middleware('permission:dashboard');
    $this->prev = $url->previous();
  }

  public function getPayment($payment_id) {
    if($payment = \Solunes\Payments\App\Payment::findId($payment_id)->checkOwner()->with('payment_items')->first()){
      $array['page'] = \Solunes\Master\App\Page::find(2);
      $array['sale'] = $sale;
      $array['sale_payments'] = $sale->sale_payments;
      $view = 'process.sale';
      if(!view()->exists($view)){
        $view = 'sales::'.$view;
      }
      return view($view, $array);
    } else {
      return redirect($this->prev)->with('message_error', 'Hubo un error al encontrar su compra.');
    }
  }

  public function getCancelPayment($payment_id) {
    if($payment = \Solunes\Payments\App\Payment::findId($payment_id)->checkOwner()->first()){
      $payment->status = 'cancelled';
      $payment->save();
      return redirect($this->prev)->with('message_success', 'Su pago fue cancelado correctamente.');
    } else {
      return redirect($this->prev)->with('message_error', 'Hubo un error al cancelar su pago.');
    }
  }
  
  public function getFinishSalePayment($sale_id, $type) {
    $sale = \Solunes\Sales\App\Sale::find($sale_id);
    $model = '\Pagostt';
    if($type=='pagostt'){
      $model = '\Pagostt';
    } else if($type=='pagatodo'){
      $model = '\Pagatodo';
    } else if($type=='banipay'){
      $model = '\Banipay';
    } else if($type=='paypal'||$type=='braintree'||$type=='payu'||$type=='neteller'){
      $model = '\OmnipayGateway';
    } else if($type=='payme'){
      $model = '\Payme';
    } else if($type=='test-payment'&&config('payments.test-payment')){
      $model = '\TestPayment';
    } else if($type=='bank-deposit'){
      $model = '\BankDeposit';
    }
    return \Payments::generateSalePayment($sale, $model, 'inicio', $type);
  }

  public function getSuccessfulSalePayment($sale_id, $type) {
    $sale = \Solunes\Sales\App\Sale::find($sale_id);
    $model = '\Pagostt';
    if($type=='pagostt'){
      $model = '\Pagostt';
    } else if($type=='paypal'||$type=='braintree'||$type=='payu'||$type=='neteller'){
      $model = '\OmnipayGateway';
    } else if($type=='payme'){
      $model = '\Payme';
    } else if($type=='test-payment'&&config('payments.test-payment')){
      $model = '\TestPayment';
    } else if($type=='bank-deposit'){
      $model = '\BankDeposit';
    }
    return \Payments::generateSalePayment($sale, $model, 'inicio', $type);
  }

  /* Ruta POST para deposito bancario */
  public function postBankDeposit(Request $request) {
    $validator = \Validator::make($request->all(), \Solunes\Payments\App\OnlineBankDeposit::$rules_send);
    $sale_payment_id = $request->input('sale_payment_id');
    if(!$validator->passes()){
      return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos obligatorios.')->withErrors($validator)->withInput();
    } else {
      $sale_payment = \Solunes\Sales\App\SalePayment::find($sale_payment_id);
      $sale = $sale_payment->sale;
      if($sale_payment->status=='holding'&&$sale = \Solunes\Sales\App\Sale::findId($sale_payment->parent_id)->checkOwner()->first()){
        $cancel_url = url('');
        $transaction = \BankDeposit::generateSalePayment($sale_payment->payment, $cancel_url);
        if($sale_payment->online_bank_deposit){
          $online_bank_deposit = $sale_payment->online_bank_deposit;
        } else {
          $online_bank_deposit = new \Solunes\Payments\App\OnlineBankDeposit;
          $online_bank_deposit->sale_payment_id = $sale_payment->id;
          $online_bank_deposit->status = 'holding';
        }
        $online_bank_deposit->parent_id = $request->input('online_bank_id');
        $online_bank_deposit->transaction_id = $transaction->id;
        $online_bank_deposit->file = \Asset::upload_file($request->file('file'), 'online-bank-deposit-file');
        $online_bank_deposit->save();
        if(config('payments.cash_params.redirect')&&config('payments.cash_params.redirect_url')){
          if(config('payments.notify_agency_on_payment')&&$sale->agency){
            \FuncNode::make_email('verify-payment', [$sale->agency->email], []);
          }
          return redirect(config('payments.cash_params.redirect_url'))->with('message_success', 'Su pago fue recibido, deberá ser confirmado en las próximas horas y le enviaremos un email confirmando la recepción del pago. ¡Muchas gracias!');
        } else {
          return redirect($this->prev)->with('message_success', 'Su pago fue recibido, deberá ser confirmado en las próximas horas y le enviaremos un email confirmando la recepción del pago. ¡Muchas gracias!');
        }
      } else {
        return redirect($this->prev)->with('message_error', 'Hubo un error al encontrar su pago.');
      }
    }
  }

  /* Ruta POST para deposito bancario */
  public function postCashPayment(Request $request) {
    if($request->has('api_request')&&$request->input('api_request')==true){
      $api_request = true;
    } else {
      $api_request = false;
    }
    $validator = \Validator::make($request->all(), \Solunes\Payments\App\CashPayment::$rules_send);
    $sale_payment_id = $request->input('sale_payment_id');
    $sale_payment = \Solunes\Sales\App\SalePayment::find($sale_payment_id);
    if(!$validator->passes()){
      return redirect($this->prev)->with('message_error', 'Debe llenar todos los campos obligatorios.')->withErrors($validator)->withInput();
    } else if(round($sale_payment->amount)>round(intval($request->input('amount')))) {
      return redirect($this->prev)->with('message_error', 'El monto introducido debe ser mayor a la compra.')->withErrors($validator)->withInput();
    } else {
      $sale = \Solunes\Sales\App\Sale::findId($sale_payment->parent_id)->checkOwner()->first();
      if($sale&&$sale_payment->status=='holding'){
        $cancel_url = url('');
        $transaction = \CashPayment::generateSalePayment($sale_payment->payment, $cancel_url);
        if($sale_payment->cash_payment){
          $cash_payment = $sale_payment->cash_payment;
        } else {
          $cash_payment = new \Solunes\Payments\App\CashPayment;
          $cash_payment->sale_payment_id = $sale_payment->id;
        }
        $cash_payment->transaction_id = $transaction->id;
        $cash_payment->amount = $request->input('amount');
        $cash_payment->save();
        $sale_payment->status = 'to-pay';
        $sale_payment->save();
        $sale->status = 'pending-delivery';
        $sale->save();
        $payment = $sale_payment->payment;
        $payment->status = 'to-pay';
        $payment->save();
        \Sales::customerSuccessfulPayment($sale, $sale->customer);
        if(config('customer.custom_successful_payment')){
            \CustomFunc::customer_successful_payment($payment);
        }
        if($api_request){
          return ['success'=>true,'message'=>'Su pedido fue procesado correctamente y será enviado en la fecha y hora solicitada.'];
        }
        return redirect($this->prev)->with('message_success', 'Muchas gracias, marcamos la orden como procesada y procederemos a realizar el cobro en el momento del envío.');
      } else {
        if($api_request){
          return ['success'=>false,'message'=>'Hubo un error al encontrar su pago.'];
        }
        return redirect($this->prev)->with('message_error', 'Hubo un error al encontrar su pago.');
      }
    }
  }

}