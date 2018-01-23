<?php 

namespace Solunes\Payments\App\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class ProcessController extends Controller {

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

}