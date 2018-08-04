<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NodesPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Módulo General de Pagos
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->string('model')->nullable();
            $table->text('content')->nullable();
            $table->boolean('active')->default(1);
            $table->boolean('automatic')->default(1);
            $table->boolean('normal_payments')->default(1);
            $table->boolean('recurrent_payments')->default(0);
            $table->timestamps();
        });
        if(config('payments.scheduled_transactions')){
            Schema::create('scheduled_transactions', function (Blueprint $table) {
                $table->increments('id');
                $table->string('customer_id')->nullable();
                $table->string('payment_code')->nullable();
                $table->string('external_payment_code')->nullable();
                $table->string('external_profile_id')->nullable();
                $table->decimal('outstanding_balance', 10, 2)->nullable();
                $table->date('profile_start_date')->nullable();
                $table->enum('billing_period', ['Day','Week','SemiMonth','Month','Year'])->default('Month');
                $table->integer('billing_frequency')->default(1);
                $table->integer('total_billing_cycles')->default(0);
                $table->decimal('initial_amount', 10, 2)->nullable()->default(0);
                $table->decimal('payment_amount', 10, 2)->nullable();
                $table->enum('method', ['paypal','other'])->default('paypal');
                $table->boolean('active')->nullable()->default(1);
                $table->timestamps();
            });
            Schema::create('scheduled_transaction_items', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable();
                $table->string('item_type')->nullable();
                $table->string('item_id')->nullable();
                $table->enum('category',['digital','phyisical'])->default('digital');
                $table->string('name')->nullable();
                $table->integer('currency_id')->nullable();
                $table->integer('quantity')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->timestamps();
            });
            Schema::create('scheduled_transaction_payments', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable();
                $table->text('callback_url')->nullable();
                $table->decimal('amount', 10, 2)->nullable();
                $table->boolean('processed')->default(0);
                $table->timestamps();
            });
        }
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->integer('currency_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->boolean('invoice')->nullable();
            $table->string('invoice_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['holding','paid','cancelled'])->default('holding');
            $table->boolean('active')->nullable()->default(1);
            $table->timestamps();
        });
        if(config('payments.invoices')){
            Schema::create('payment_invoices', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable();
                $table->string('name')->nullable();
                $table->string('invoice_code')->nullable();
                $table->string('invoice_url')->nullable();
                $table->string('invoice_number')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_nit')->nullable();
                $table->decimal('amount', 10, 2)->nullable();
                $table->timestamps();
            });
        }
        if(config('payments.invoices')&&config('payments.enable_cycle')){
            Schema::table('transaction_invoices', function (Blueprint $table) {
                $table->string('billing_cycle_dosage')->nullable();
                $table->string('billing_cycle_start_date')->nullable();
                $table->string('billing_cycle_end_date')->nullable();
                $table->string('billing_cycle_eticket')->nullable();
                $table->string('billing_cycle_legend')->nullable();
                $table->string('billing_cycle_parallel')->nullable();
                $table->string('billing_cycle_invoice_title')->nullable();
                $table->string('company_code')->nullable();
            });
        }
        if(config('payments.shipping')){
            Schema::create('payment_shippings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable();
                $table->string('name')->nullable();
                $table->string('contact_name')->nullable();
                $table->string('address')->nullable();
                $table->string('address_2')->nullable();
                $table->string('city')->nullable();
                $table->string('region')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country_code')->nullable();
                $table->string('phone')->nullable();
                $table->decimal('price', 10, 2)->nullable();
                $table->timestamps();
            });
        }
        Schema::create('payment_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable();
            $table->string('item_type')->nullable();
            $table->string('item_id')->nullable();
            $table->string('name')->nullable();
            $table->string('detail')->nullable();
            $table->integer('currency_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('customer_id')->nullable();
            $table->text('callback_url')->nullable();
            $table->string('payment_code')->nullable();
            $table->integer('payment_method_id')->default(1);
            $table->text('external_payment_code')->nullable();
            $table->enum('status', ['holding','paid','cancelled'])->default('holding');
            $table->timestamps();
        });
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->boolean('processed')->default(0);
            $table->timestamps();
        });
        if(config('payments.invoices')){
            Schema::create('transaction_invoices', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('parent_id')->nullable();
                $table->string('name')->nullable();
                $table->string('invoice_code')->nullable();
                $table->string('invoice_url')->nullable();
                $table->string('nit_company')->nullable();
                $table->string('invoice_number')->nullable();
                $table->string('auth_number')->nullable();
                $table->string('control_code')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_nit')->nullable();
                $table->enum('invoice_type', ['E','C'])->nullable();
                $table->decimal('amount', 10, 2)->nullable();
                $table->timestamps();
            });
        }
        if(config('payments.online_banks')){
            Schema::create('online_banks', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('account_number')->nullable();
                $table->integer('currency_id')->nullable();
                $table->string('image')->nullable();
                $table->text('content')->nullable();
                $table->timestamps();
            });
            Schema::create('online_bank_deposits', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('online_bank_id')->unsigned();
                $table->integer('payment_transaction_id')->unsigned();
                $table->enum('status', ['holding','confirmed','denied'])->nullable()->default('holding');
                $table->string('image')->nullable();
                $table->text('observations')->nullable();
                $table->timestamps();
                $table->foreign('online_bank_id')->references('id')->on('online_banks')->onDelete('cascade');
                $table->foreign('payment_transaction_id')->references('id')->on('payment_transactions')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Módulo General de PagosTT
        Schema::dropIfExists('online_bank_deposits');
        Schema::dropIfExists('online_banks');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('transaction_invoices');
        Schema::dropIfExists('transaction_payments');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('payment_invoices');
        Schema::dropIfExists('payment_items');
        Schema::dropIfExists('payment_shippings');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('online_transaction_payments'); // BORRAR
        Schema::dropIfExists('online_transaction_items'); // BORRAR
        Schema::dropIfExists('online_transaction_shippings'); // BORRAR
        Schema::dropIfExists('online_transactions'); // BORRAR
        Schema::dropIfExists('scheduled_transaction_payments');
        Schema::dropIfExists('scheduled_transaction_items');
        Schema::dropIfExists('scheduled_transactions');
        Schema::dropIfExists('payment_methods');

    }
}
