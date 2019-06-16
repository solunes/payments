<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;
use Paypalpayment;

class Paypal {
    
    public static function generateSalePayment($payment, $cancel_url) {
        $custom_app_key = NULL;
        if(config('payments.pagostt_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($payment->customer_id, false, false, $custom_app_key);
            $payment_object = \PagosttBridge::getPayment($payment->id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($payment->customer_id, false, false, $custom_app_key);
            $payment_object = \Customer::getPayment($payment->id, $custom_app_key);
        }
        if($customer&&$payment){
          $payment_object = \Payments::getShippingCost($payment_object, [$payment->id]);
          $payments_transaction = \Payments::generatePaymentTransaction($payment->customer_id, [$payment->id], 'paypal', $payment_object['amount']);
          $parameters = \Paypal::generateTransactionArray($customer, $payment, $payments_transaction, $custom_app_key);
          $api_url = \Paypal::generateTransactionQuery($payments_transaction, $parameters);
          if($api_url){
            return $api_url;
          } else {
            return NULL;
          }
        } else {
          \Log::info('Error, no hay Customer ('.json_encode($customer).') y Payment ('.json_encode($payment).')');
          return NULL;
        }
    }

    public static function generateSalePaymentBackup($payment, $cancel_url) {
        $custom_app_key = NULL;
        if(config('payments.pagostt_params.enable_bridge')){
            $customer = \PagosttBridge::getCustomer($payment->customer_id, false, false, $custom_app_key);
            $payment_object = \PagosttBridge::getPayment($payment->id, $custom_app_key);
        } else {
            $customer = \Customer::getCustomer($payment->customer_id, false, false, $custom_app_key);
            $payment_object = \Customer::getPayment($payment->id, $custom_app_key);
        }
        /*if($customer&&$payment){
          $payment = \Payments::getShippingCost($payment, [$payment_item->id]);
          $pagostt_transaction = \Paypal::generatePaymentTransaction($payment_item->customer_id, [$payment_item->id], $payment['amount']);
          $final_fields = \Paypal::generateTransactionArray($customer, $payment, $pagostt_transaction, $custom_app_key);
          $api_url = \Paypal::generateTransactionQuery($pagostt_transaction, $final_fields);
          if($api_url){
            return $api_url;
          } else {
            return NULL;
          }
        } else {
          \Log::info('Error, no hay Customer ('.json_encode($customer).') y Payment ('.json_encode($payment).')');
          return NULL;
        }*/
        $payment_object = \Payments::getShippingCost($payment_object, [$payment->id]);
        $payments_transaction = \Payments::generatePaymentTransaction($payment->customer_id, [$payment->id], 'paypal', $payment_object['amount']);
        $shipping_cost = 0;
        $tax_cost = 0;
        $subtotal_cost = 0;
        $callback_url = \Payments::generatePaymentCallback($payments_transaction->payment_code);
        $cancel_url = url('inicio');

        // ### Address
        // Base Address object used as shipping or billing
        // address in a payment. [Optional]
        $shippingAddress = NULL;
        /*$payment->load('payment_shippings');
        if(count($payment->payment_shippings)>0){
          foreach($payment->payment_shippings as $shipping){
            $shippingAddress= Paypalpayment::shippingAddress();
            $shippingAddress->setLine1($shipping->address)
            ->setLine2($shipping->address_2)
            ->setCity($shipping->city)
            ->setState($shipping->region)
            ->setPostalCode($shipping->postal_code)
            ->setCountryCode($shipping->country_code)
            ->setPhone($shipping->phone)
            ->setRecipientName($shipping->contact_name);
            $shipping_cost += $shipping->price;
          }
        }*/

        // ### Payer
        // A resource representing a Payer that funds a payment
        // Use the List of `FundingInstrument` and the Payment Method
        // as 'credit_card'
        $payer = Paypalpayment::payer();
        $payer->setPaymentMethod("paypal");

        $arrayToAdd = [];
        $payment->load('payment_items');
        foreach($payment->payment_items as $subitem){
            $item = Paypalpayment::item();
            $item->setName($subitem->name)
            ->setDescription($subitem->detail)
            ->setCurrency($subitem->currency->code)
            ->setQuantity($subitem->quantity)
            ->setTax($subitem->tax)
            ->setPrice($subitem->price);
            $subtotal_cost += ($subitem->price * $subitem->quantity);
            $tax_cost += ($subitem->price * $subitem->quantity * $subitem->tax);
            array_push($arrayToAdd, $item);
        }

        $itemList = Paypalpayment::itemList();
        $itemList->setItems($arrayToAdd);
        if($shippingAddress){
            $itemList->setShippingAddress($shippingAddress);
        }


        $details = Paypalpayment::details();
        $details->setShipping($shipping_cost)
                ->setTax($tax_cost)
                //total of items prices
                ->setSubtotal($subtotal_cost);

        //Payment Amount
        $amount = Paypalpayment::amount();
        $amount->setCurrency($payment->currency->code)
                // the total is $17.8 = (16 + 0.6) * 1 ( of quantity) + 1.2 ( of Shipping).
                ->setTotal($shipping_cost+$tax_cost+$subtotal_cost)
                ->setDetails($details);

        // ### Transaction
        // A transaction defines the contract of a
        // payment - what is the payment for and who
        // is fulfilling it. Transaction is created with
        // a `Payee` and `Amount` types

        $transaction = Paypalpayment::transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($payment->name)
            ->setInvoiceNumber(uniqid());

        // ### Payment
        // A Payment Resource; create one using
        // the above types and intent as 'sale'

        $redirectUrls = Paypalpayment::redirectUrls();
        $redirectUrls->setReturnUrl($callback_url)
            ->setCancelUrl($cancel_url);

        $payment = Paypalpayment::payment();

        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        try {
            // ### Create Payment
            // Create a payment by posting to the APIService
            // using a valid ApiContext
            // The return object contains the status;
            $payment->create(Paypalpayment::apiContext());
        } catch (\PPConnectionException $ex) {
            return response()->json(["error" => $ex->getMessage()], 400);
        }

        //return response()->json([$payment->toArray(), 'approval_url' => $payment->getApprovalLink()], 200);
        if($payment->getApprovalLink()){
            return $payment->getApprovalLink();
        } else {
            return NULL;
        }
    }

    public static function generateTransactionArray($customer, $payment, $payments_transaction, $custom_app_key){
        $items = [];
        $amount = 0;
        $payment->load('payment_items');
        foreach($payment->payment_items as $payment_item){
            $amount += $payment_item->amount;
            $items[] = ['name'=>$payment_item->name,'description'=>$payment_item->detail,'sku'=>$payment_item->item_type.'-'.$payment_item->item_id,'unit_amount'=>['currency_code'=>'USD','value'=>$payment_item->price],'quantity'=>$payment_item->quantity,'item_total'=>$payment_item->amount,'category'=>'PHYSICAL_GOODS'];
        }
        $application_context = ['shipping_preference'=>'SET_PROVIDED_ADDRESS','user_action'=>'PAY_NOW','return_url'=>url('inicio'),'cancel_url'=>url('inicio')];
        $amount_text = ['currency_code'=>'USD','value'=>$amount, 'breakdown'=>['item_total'=>['currency_code'=>'USD','value'=>$amount]]];
        $purchase = ['reference_id'=>'PAY-'.$payment->id,'description'=>'Venta en Linea','invoice_id'=>'INVOICE-'.$payment->id,'custom_id'=>'INVOICE-'.$payment->id,'amount'=>$amount_text,'items'=>$items,'application_context'=>$application_context];
        $parameters = ['intent'=>'CAPTURE', 'purchase_units'=>[$purchase]];
        return $parameters;
    }

    public static function generateTransactionQuery($transaction, $parameters){
        $url = \Paypal::getUrl(2);
        $access_token = \Paypal::getAccessToken();
        $response = \External::guzzlePost($url, 'checkout/orders', $parameters, ['Authorization'=>'Bearer '.$access_token]);
        // Guardado de transaction_id generado por PagosTT
        $transaction->external_payment_code = $response['id'];
        $transaction->save();
        if($response['id']){
            $url = \Paypal::getSiteUrl(0);
            return $url.'/checkoutnow?token='.$response['id'];
        } else {
            return NULL;
        }
    }

    public static function getUrl($version = 1){
        $sandboxUrl = 'https://api.sandbox.paypal.com';
        $liveUrl = 'https://api.paypal.com';
        if(config('payments.paypal_params.testing')){
            if($version==0){
                return $sandboxUrl;
            }
            return $sandboxUrl.'/v'.$version;
        } else {
            if($version==0){
                return $liveUrl;
            }
            return $liveUrl.'/v'.$version;
        }
    }

    public static function getSiteUrl($version = 1){
        $sandboxUrl = 'https://www.sandbox.paypal.com';
        $liveUrl = 'https://www.paypal.com';
        if(config('payments.paypal_params.testing')){
            if($version==0){
                return $sandboxUrl;
            }
            return $sandboxUrl.'/v'.$version;
        } else {
            if($version==0){
                return $liveUrl;
            }
            return $liveUrl.'/v'.$version;
        }
    }

    public static function getToken(){
        $url = \Paypal::getUrl();
        if(config('payments.paypal_params.testing')){
            $client_id = config('payments.paypal_params.sandbox_api_client');
            $secret = config('payments.paypal_params.sandbox_api_secret');
        } else {
            $client_id = config('payments.paypal_params.live_api_client');
            $secret = config('payments.paypal_params.live_api_secret');
        }
        $headers = ['auth_username'=>$client_id, 'auth_password'=>$secret, 'Content-Type'=>'application/x-www-form-urlencoded', 'Accept'=>'application/json', 'Accept-Language'=>'en_US'];
        $response = \External::guzzlePost($url, 'oauth2/token', ['grant_type'=>'client_credentials'], $headers);
        if(isset($response['access_token'])){
            \Log::info('Acccess Token Response: '.json_encode($response['access_token']));
            return $response['access_token'];
        }
        return $response;
    }

    public static function getAccessToken(){
        if(config('payments.paypal_params.testing')){
            $access_token = config('payments.paypal_params.sandbox_access_token');
        } else {
            $access_token = config('payments.paypal_params.live_access_token');
        }
        if(!$access_token){
            $access_token = \Paypal::getToken();
        }
        return $access_token;
    }

}