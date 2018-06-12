<?php

namespace Solunes\Payments\App\Listeners;

class TransactionPaymentCreated {

    public function handle($event) {
        if($payment = $event->payment){
            $payment->status = 'paid';
            $payment->active = 1;
            $payment->save();
        }
        return $event;
    }

}