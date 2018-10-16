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
        if(config('payments.shipping')){
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
        if(config('payments.online_banks')){
            $node_online_bank = \Solunes\Master\App\Node::create(['name'=>'online-bank', 'location'=>'payments', 'folder'=>'parameters']);
            \Solunes\Master\App\Node::create(['name'=>'online-bank-deposit', 'location'=>'payments', 'folder'=>'payments', 'type'=>'child', 'parent_id'=>$node_online_bank->id]);
        }

        // Crear Métodos de Pago por Defecto
        if(config('payments.manual')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Pago Manual', 'code'=>'manual-payment', 'model'=>NULL, 'content'=>'<p>Método de pagos registrados manualmente por el administrador.</p>', 'automatic'=>0, 'active'=>0]);
        }
        if(config('payments.bank-deposit')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'Transferencia Bancaria', 'code'=>'bank-deposit', 'model'=>'BankDeposit', 'content'=>'<p>Realiza una transferencia bancaria a:</p>', 'automatic'=>0]);
        }
        if(config('payments.pagostt')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'PagosTT', 'code'=>'pagostt', 'model'=>'Pagostt', 'content'=>'<p>Realiza una transferencia por medio de PagosTT. Un canal de pagos integrado a:<br>- Tarjetas de Crédito y Débito<br>- PagosNet (Más de 3000 agencias en Bolivia)<br>Tigo Money (Puede utilizarse sin Tigo)<br>BNB (Banca por Internet)</p>']);
        }
        if(config('payments.paypal')){
            \Solunes\Payments\App\PaymentMethod::create(['name'=>'PayPal', 'code'=>'paypal', 'model'=>'Paypal', 'content'=>'<p>Realiza una transferencia por tu cuenta de PayPal o paga por tarjeta de crédito desde cualquier parte del mundo. Sabemos que tu seguridad es importante y es por eso que trabajamos con la empresa de pagos más grande del mundo.</p>', 'recurrent_payments'=>1]);
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