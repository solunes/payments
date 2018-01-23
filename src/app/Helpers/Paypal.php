<?php 

namespace Solunes\Payments\App\Helpers;

use Validator;
use Paypalpayment;

class Paypal {

    public static function generateSalePayment($payment, $cancel_url) {
        $payments_transaction = \Payments::generatePaymentTransaction($payment, 'paypal');
        $callback_url = \Payments::generatePaymentCallback($payments_transaction->payment_code);
        $shipping_cost = 0;
        $tax_cost = 0;
        $subtotal_cost = 0;

        // ### Address
        // Base Address object used as shipping or billing
        // address in a payment. [Optional]
        $shippingAddress = NULL;
        $payment->load('payment_shippings');
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
        }

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

}