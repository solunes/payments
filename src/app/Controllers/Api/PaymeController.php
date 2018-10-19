<?php

namespace Solunes\Payments\App\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller\Api;

class PaymeController extends BaseController {

    public function getSuccessfulPayment($payment_code, $external_payment_code){
        \Log::info('Successful transaction: '.$payment_code.' | '.$external_payment_code.' | '.json_encode(request()->all()));
        if($payment_code&&request()->has('transaction_id')){
            $api_transaction = false;
            if($external_payment_code&&$transaction = \Solunes\Payments\App\Transaction::where('payment_code',$payment_code)->where('external_payment_code',$external_payment_code)->where('status','holding')->first()){
                $api_transaction = true;
            } else if($transaction = \Solunes\Payments\App\Transaction::where('payment_code',$payment_code)->where('external_payment_code',request()->input('transaction_id'))->where('status','holding')->first()){
                $api_transaction = false;
            } else if($transaction = \Solunes\Payments\App\Transaction::where('payment_code',$payment_code)->where('external_payment_code',request()->input('transaction_id'))->where('status','paid')->first()){
                \Pagostt::putInoviceParameters($transaction);
                \Pagostt::putPaymentInvoice($transaction);
                return redirect(config('payments.redirect_after_payment'))->with('message_success', 'Su pago fue realizado correctamente');
            } else if($transaction = \Solunes\Payments\App\Transaction::where('payment_code',$payment_code)->where('external_payment_code',request()->input('transaction_id'))->where('status','cancelled')->first()){
                return redirect(config('payments.redirect_after_payment'))->with('message_success', 'Su pago fue cancelado. Para más información contáctese con el administrador.');
            } else {
                throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('Pago no encontrado en verificación.');
            }
            \Pagostt::putInoviceParameters($transaction);
            \Pagostt::putPaymentInvoice($transaction);
            $transaction->status = 'paid';
            $transaction->save();
            if(config('payments.pagostt_params.enable_bridge')){
                $payment_registered = \PagosttBridge::transactionSuccesful($transaction);
            } else {
                $payment_registered = \Customer::transactionSuccesful($transaction);
            }
            if(config('payments.pagostt_params.notify_email')){
                if(config('payments.pagostt_params.enable_bridge')){
                    $customer = \PagosttBridge::getCustomer($transaction->customer_id);
                } else {
                    $customer = \Customer::getCustomer($transaction->customer_id);
                }
                \Log::info('Successful Transaction Email: '.$customer['email']);
                if(!config('payments.pagostt_params.testing')){
                    \Mail::send('payments::emails.successful-payment', ['amount'=>$transaction->amount, 'email'=>$customer['email']], function($m) use($customer) {
                        if($customer['name']){
                            $name = $customer['name'];
                        } else {
                            $name = 'Cliente';
                        }
                        $m->to($customer['email'], $name)->subject(config('solunes.app_name').' | '.trans('payments::mail.successful_payment_title'));
                    });
                }
            }
            if($api_transaction){
                return $this->response->array(['payment_registered'=>$payment_registered])->setStatusCode(200);
            } else {
                return redirect(config('payments.redirect_after_payment'))->with('message_success', 'Su pago fue realizado correctamente.');
            }
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('Operación no permitida.');
        }
    }

}