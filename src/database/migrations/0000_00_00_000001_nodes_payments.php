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
        Schema::create('online_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('customer_id')->nullable();
            $table->string('payment_code')->nullable();
            $table->text('external_payment_code')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->enum('method', ['pagostt','paypal','payme','tigomoney','pagosnet','other'])->default('pagostt');
            $table->enum('status', ['holding','paid','cancelled'])->default('holding');
            $table->boolean('active')->nullable()->default(1);
            $table->timestamps();
        });
        Schema::create('online_transaction_shippings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable();
            $table->string('name')->nullable();
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
        Schema::create('online_transaction_items', function (Blueprint $table) {
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
            $table->timestamps();
        });
        Schema::create('online_transaction_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->nullable();
            $table->text('callback_url')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->boolean('processed')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Módulo General de PagosTT
        Schema::dropIfExists('online_transaction_payments');
        Schema::dropIfExists('online_transaction_items');
        Schema::dropIfExists('online_transaction_shippings');
        Schema::dropIfExists('online_transactions');
        Schema::dropIfExists('scheduled_transaction_payments');
        Schema::dropIfExists('scheduled_transaction_items');
        Schema::dropIfExists('scheduled_transactions');

    }
}
