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
        \Solunes\Payments\App\OnlineTransactionPayment::truncate();
        \Solunes\Payments\App\OnlineTransactionShipping::truncate();
        \Solunes\Payments\App\OnlineTransactionItem::truncate();
        \Solunes\Payments\App\OnlineTransaction::truncate();
        \Solunes\Payments\App\ScheduledTransactionPayment::truncate();
        \Solunes\Payments\App\ScheduledTransactionItem::truncate();
        \Solunes\Payments\App\ScheduledTransaction::truncate();
        \Solunes\Payments\App\PaymentMethod::truncate();
    }
}