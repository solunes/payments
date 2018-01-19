<?php

namespace Solunes\Payments\Database\Seeds;

use Illuminate\Database\Seeder;
use DB;

class TruncateSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Solunes\Payments\App\OnlineBankDeposit::truncate();
        \Solunes\Payments\App\OnlineBank::truncate();
        \Solunes\Payments\App\PaymentTransaction::truncate();
        \Solunes\Payments\App\PaymentShipping::truncate();
        \Solunes\Payments\App\PaymentItem::truncate();
        \Solunes\Payments\App\Payment::truncate();
        \Solunes\Payments\App\ScheduledTransactionPayment::truncate();
        \Solunes\Payments\App\ScheduledTransactionItem::truncate();
        \Solunes\Payments\App\ScheduledTransaction::truncate();
        \Solunes\Payments\App\PaymentMethod::truncate();
    }
}