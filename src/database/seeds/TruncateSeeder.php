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
        \Solunes\Payments\App\OnlineTransactionShipping::truncate();
        \Solunes\Payments\App\OnlineTransactionItem::truncate();
        \Solunes\Payments\App\OnlineTransaction::truncate();
    }
}