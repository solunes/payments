<?php

namespace Solunes\Payments\App\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller\Api;

class PaymentsController extends BaseController {

    public function getCustomerPayments($app_key, $customer_id, $external_payment_code = NULL){
        if($app_key==config('payments.pagostt_params.app_key')||in_array($app_key, config('payments.pagostt_params.custom_app_keys'), true)){
            if(config('payments.pagostt_params.enable_bridge')){
                $customer = \PagosttBridge::getCustomer($customer_id, true, true, $app_key);
            } else {
                $customer = \Customer::getCustomer($customer_id, true, true, $app_key);
            }
            if($customer&&is_array($customer)){
                $pending_payments = $customer['pending_payments'];
                $final_pending_payments = [];
                foreach($pending_payments as $payment_id => $pending_payment){
                    $final_pending_payments[$payment_id] = $pending_payment;
                    foreach($pending_payment['items'] as $key => $item){
                        $new_item = json_decode($item, true);
                        $transaction = \Pagostt::generatePaymentTransaction($customer['id'], [$payment_id], $pending_payment['amount']);
                        if($external_payment_code){
                            $transaction->external_payment_code = $external_payment_code;
                            $transaction->save();
                        }
                        $callback_url = \Pagostt::generatePaymentCallback($transaction->payment_code, $external_payment_code);
                        $new_item['appkey_empresa_final'] = $app_key;
                        $new_item['call_back_url'] = $callback_url;
                        $new_item = json_encode($new_item, JSON_UNESCAPED_SLASHES);
                        $new_item = \Pagostt::encrypt($new_item);
                        $final_pending_payments[$payment_id]['items'][$key] = urlencode($new_item);
                    }
                }
                return $this->response->array(['enabled'=>config('payments.pagostt_params.customer_recurrent_payments'), 'app_key'=>$app_key, 'app_name'=>config('payments.pagostt_params.app_name'), 'codigo_cliente'=>$customer_id, 'external_payment_code'=>$external_payment_code, 'pagos_pendientes'=>$final_pending_payments])->setStatusCode(200);
            } else {
                return $this->response->array(['enabled'=>config('payments.pagostt_params.customer_recurrent_payments'), 'app_key'=>$app_key, 'app_name'=>config('payments.pagostt_params.app_name'), 'codigo_cliente'=>false, 'pagos_pendientes'=>[]])->setStatusCode(200);
            }
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('El token no fue autorizado.');
        } 
    }

    public function getSuccessfulPayment($payment_code){
        \Log::info('Successful transaction: '.$payment_code.' | '.json_encode(request()->all()));
        if(!$payment_code){
            \Log::info('No se cuenta con un payment_code.');
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('No se cuenta con un payment_code.');
        }
        $checkItem = \DataManager::putUniqueValue('succesful-transactions-codes', $payment_code);
        if(!$checkItem){
            \Log::info('Transacción encontrada, no se accede de nuevo.');
            return redirect(config('payments.redirect_after_payment'))->with('message_success', 'Su pago fue realizado correctamente');
        } 
        \Log::info('Transaccion aceptada, procesando: '.$payment_code);
        $api_transaction = false;
        if($transaction = \Solunes\Payments\App\Transaction::where('payment_code',$payment_code)->where('status','holding')->first()){
            $api_transaction = false;
            \OmnipayGateway::checkExternalTransactionCode($transaction);
        } else if($transaction = \Solunes\Payments\App\Transaction::where('payment_code',$payment_code)->where('status','paid')->first()){
            \OmnipayGateway::checkExternalTransactionCode($transaction);
            return redirect(config('payments.redirect_after_payment'))->with('message_success', 'Su pago fue realizado correctamente');
        } else if($transaction = \Solunes\Payments\App\Transaction::where('payment_code',$payment_code)->where('status','cancelled')->first()){
            \OmnipayGateway::checkExternalTransactionCode($transaction);
            return redirect(config('payments.redirect_after_payment'))->with('message_success', 'Su pago fue cancelado. Para más información contáctese con el administrador.');
        } else {
            throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException('Pago no encontrado en verificación.');
        }
        if($transaction->payment_method->code!='test-payment'&&$transaction->payment_method->code!='bank-deposit'){
            $request_object = request()->all();
            \OmnipayGateway::completeTransactionQuery($transaction, $payment_code,$request_object);
        }
        $transaction->status = 'paid';
        $transaction->save();
        \Log::info('transaccion procesada a full');
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
            if(!config('payments.pagostt_params.testing')&&filter_var($customer['email'], FILTER_VALIDATE_EMAIL)){
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
    }

}