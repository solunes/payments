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

  public function getFinishSalePayment($sale_id, $type) {
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
    }
    return \Payments::generateSalePayment($sale, $model, 'inicio', $type);
  }

}