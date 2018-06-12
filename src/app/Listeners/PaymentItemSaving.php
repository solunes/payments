<?php

namespace Solunes\Payments\App\Listeners;

class PaymentItemSaving {

    public function handle($event) {
        if(!$event->quantity){
            $event->quantity = 1;
        }
        if(!$event->amount){
            $event->amount = ($event->price * $event->quantiy) + $event->tax;
        }
        if(!$event->price){
            $event->price = round(($event->amount-$event->tax) / $event->quantiy, 2);
        }
        return $event;
    }

}