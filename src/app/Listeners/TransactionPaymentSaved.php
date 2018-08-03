<?php

namespace Solunes\Payments\App\Listeners;

class TransactionPaymentSaved {

    public function handle($event) {
        if($event->processed&&$payment = $event->payment){
            $payment->status = 'paid';
            $payment->active = 1;
            $payment->save();
        }
        return $event;
    }

}