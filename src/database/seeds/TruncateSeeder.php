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
        if(config('payments.cash')){
            \Solunes\Payments\App\CashPayment::truncate();
        }
        if(config('payments.online_banks')||config('payments.bank-deposit')){
            \Solunes\Payments\App\OnlineBankDeposit::truncate();
            \Solunes\Payments\App\OnlineBank::truncate();
        }
        if(config('payments.pagostt_params.enable_preinvoice')){
            \Solunes\Payments\App\PreinvoiceItem::truncate();
            \Solunes\Payments\App\Preinvoice::truncate();
        }
        if(config('payments.invoices')){
            \Solunes\Payments\App\TransactionInvoice::truncate();
        }
        \Solunes\Payments\App\TransactionPayment::truncate();
        \Solunes\Payments\App\Transaction::truncate();
        if(config('payments.invoices')){
            \Solunes\Payments\App\PaymentInvoice::truncate();
        }
        if(config('payments.shipping')||config('sales.delivery')){
            \Solunes\Payments\App\PaymentShipping::truncate();
        }
        \Solunes\Payments\App\PaymentItem::truncate();
        \Solunes\Payments\App\Payment::truncate();
        if(config('payments.scheduled_transactions')){
            \Solunes\Payments\App\ScheduledTransactionPayment::truncate();
            \Solunes\Payments\App\ScheduledTransactionItem::truncate();
            \Solunes\Payments\App\ScheduledTransaction::truncate();
        }
        \Solunes\Payments\App\PaymentMethod::truncate();
    }
}