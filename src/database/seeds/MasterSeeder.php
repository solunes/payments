<?php

namespace Solunes\Payments\Database\Seeds;

use Illuminate\Database\Seeder;
use DB;

class MasterSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Módulo General de Empresa ERP
        $node_payment_method = \Solunes\Master\App\Node::create(['name'=>'payment-method', 'location'=>'payments', 'folder'=>'parameters']);
        if(config('payments.scheduled_transactions')){
            $node_scheduled_transaction = \Solunes\Master\App\Node::create(['name'=>'scheduled-transaction', 'location'=>'payments', 'folder'=>'payments']);
            \Solunes\Master\App\Node::create(['name'=>'scheduled-transaction-item', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_scheduled_transaction->id]);
            \Solunes\Master\App\Node::create(['name'=>'scheduled-transaction-payment', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_scheduled_transaction->id]);
        }
        $node_payment = \Solunes\Master\App\Node::create(['name'=>'payment', 'location'=>'payments', 'folder'=>'payments']);
        \Solunes\Master\App\Node::create(['name'=>'payment-item', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_payment->id]);
        if(config('payments.invoices')){
            \Solunes\Master\App\Node::create(['name'=>'payment-invoice', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_payment->id]);
        }
        if(config('payments.shipping')||config('sales.delivery')){
            \Solunes\Master\App\Node::create(['name'=>'payment-shipping', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_payment->id]);
        }
        $node_transaction = \Solunes\Master\App\Node::create(['name'=>'transaction', 'location'=>'payments', 'folder'=>'payments']);
        \Solunes\Master\App\Node::create(['name'=>'transaction-payment', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_transaction->id]);
        if(config('payments.invoices')){
            \Solunes\Master\App\Node::create(['name'=>'transaction-invoice', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_transaction->id]);
        }
        if(config('payments.pagostt_params.enable_preinvoice')){
            $node_preinvoice = \Solunes\Master\App\Node::create(['name'=>'preinvoice', 'location'=>'payments', 'folder'=>'parameters']);
            \Solunes\Master\App\Node::create(['name'=>'preinvoice-item', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_preinvoice->id]);
        }
        if(config('payments.online_banks')||config('payments.bank-deposit')){
            $node_online_bank = \Solunes\Master\App\Node::create(['name'=>'online-bank', 'location'=>'payments', 'folder'=>'parameters']);
            \Solunes\Master\App\Node::create(['name'=>'online-bank-deposit', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_online_bank->id]);
            $image_folder = \Solunes\Master\App\ImageFolder::create(['site_id'=>1, 'name'=>'online-bank-image', 'extension'=>'jpg']);
            \Solunes\Master\App\ImageSize::create(['parent_id'=>$image_folder->id, 'code'=>'normal', 'type'=>'resize', 'width'=>'400']);
            $image_folder = \Solunes\Master\App\ImageFolder::create(['site_id'=>1, 'name'=>'online-bank-deposit-image', 'extension'=>'jpg']);
            \Solunes\Master\App\ImageSize::create(['parent_id'=>$image_folder->id, 'code'=>'normal', 'type'=>'original', 'width'=>'600']);
            \Solunes\Master\App\ImageSize::create(['parent_id'=>$image_folder->id, 'code'=>'thumb', 'type'=>'resize', 'width'=>'300']);
        }
        if(config('payments.cash')){
            $node_cash_payment = \Solunes\Master\App\Node::create(['name'=>'cash-payment', 'location'=>'payments', 'folder'=>'parameters']);
        }

        // Crear Métodos de Pago por Defecto
        if(config('payments.manual')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Pago Manual', 'code'=>'manual-payment', 'model'=>NULL, 'content'=>'<p>Método de pagos registrados manualmente por el administrador.</p>', 'automatic'=>0, 'active'=>0]);
        }
        if(config('payments.cash')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Pago en Efectivo', 'code'=>'cash-payment', 'model'=>'CashPayment', 'content'=>'<p>Paga directamente en efectivo cuando recibas tu pedido en tu domicilio.</p>', 'automatic'=>0, 'active'=>1]);
        }
        if(config('payments.bank-deposit')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Transferencia Bancaria', 'code'=>'bank-deposit', 'model'=>'BankDeposit', 'content'=>'<p>Realiza una transferencia bancaria corriente y directa.</p>', 'automatic'=>0]);
            \Solunes\Payments\App\OnlineBank::create(['name'=>'Banco BISA', 'account_number'=>'240550-401-7', 'currency_id'=>'1', 'content'=>'<p>Caja de Ahorros a nombre de Eduardo Mejia Silva (Carnet de Identidad: 4768578 LP)</p>']);
        }
        if(config('payments.pagostt')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Libelula', 'code'=>'pagostt', 'model'=>'Pagostt', 'content'=>'<p>Realiza una transferencia por medio de Libelula, un canal de pagos integrado a los siguientes canales:</p><ul><li>Tarjetas de Crédito y Débito</li><li>Pagos Simple por QR</li><li>Tigo Money (Puede utilizarse sin Tigo)</li><li>BNB (Banca por Internet)</li><li>BCP (Banca por Internet)</li></ul>']);
        }
        if(config('payments.paypal')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'PayPal', 'code'=>'paypal', 'model'=>'Paypal', 'content'=>'<p>Realiza una transferencia por tu cuenta de PayPal o paga por tarjeta de crédito desde cualquier parte del mundo. Sabemos que tu seguridad es importante y es por eso que trabajamos con la empresa de pagos más grande del mundo.</p>', 'recurrent_payments'=>1]);
        }
        if(config('payments.payu')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'PayU', 'code'=>'payu', 'model'=>'Payu', 'content'=>'<p>Realiza un pago a través de tu tarjeta de crédito/débito:</p>', 'active'=>1]);
        }
        if(config('payments.neteller')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Neteller', 'code'=>'neteller', 'model'=>'Neteller', 'content'=>'<p>Realiza un pago a través de tu tarjeta de crédito/débito:</p>', 'active'=>1]);
        }
        if(config('payments.payme')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'PayMe', 'code'=>'payme', 'model'=>'Payme', 'content'=>'<p>Realiza un pago a través de tu tarjeta de crédito/débito:</p>', 'active'=>0]);
        }
        if(config('payments.tigo-money')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Tigo Money', 'code'=>'tigo-money', 'model'=>'TigoMoney', 'content'=>'<p>Realiza una transferencia bancaria a:</p>', 'active'=>0]);
        }
        if(config('payments.pagosnet')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'PagosNet', 'code'=>'pagosnet', 'model'=>'PagosNet', 'content'=>'<p>Realiza una transferencia bancaria a:</p>', 'active'=>0]);
        }
        if(config('payments.pagatodo')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'PagaTodo360', 'code'=>'pagatodo', 'model'=>'Pagatodo', 'content'=>'<p>Realiza tu pago a través de tarjeta de crédito / débito y pagos a través de Simple QR.</p>']);
        }
        if(config('payments.banipay')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Banipay', 'code'=>'banipay', 'model'=>'Banipay', 'content'=>'<p>Realiza tu pago a través de tarjeta de crédito / débito y pagos a través de Simple QR.</p>']);
        }
        if(config('payments.test-payment')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Test Payment', 'code'=>'test-payment', 'model'=>'TestPayment', 'content'=>'<p>Realiza un pago de prueba.</p>', 'active'=>1]);
        }

        // Usuarios
        $admin = \Solunes\Master\App\Role::where('name', 'admin')->first();
        $member = \Solunes\Master\App\Role::where('name', 'member')->first();
        if(!\Solunes\Master\App\Permission::where('name','payments')->first()){
            $payments_perm = \Solunes\Master\App\Permission::create(['name'=>'payments', 'display_name'=>'Pagos']);
            $admin->permission_role()->attach([$payments_perm->id]);
        }
        if(!\Solunes\Master\App\Permission::where('name','manual_payments')->first()){
            $manual_payments_perm = \Solunes\Master\App\Permission::create(['name'=>'manual_payments', 'display_name'=>'Pagos en Caja']);
            $admin->permission_role()->attach([$manual_payments_perm->id]);
        }
        
    }
}