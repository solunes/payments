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
        $node_payment_method = \Solunes\Master\App\Node::create(['name'=>'payment-method', 'location'=>'payments', 'folder'=>'payments']);
        $node_scheduled_transaction = \Solunes\Master\App\Node::create(['name'=>'scheduled-transaction', 'location'=>'payments', 'folder'=>'payments']);
        \Solunes\Master\App\Node::create(['name'=>'scheduled-transaction-item', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_scheduled_transaction->id]);
        \Solunes\Master\App\Node::create(['name'=>'scheduled-transaction-payment', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_scheduled_transaction->id]);
        $node_online_transaction = \Solunes\Master\App\Node::create(['name'=>'online-transaction', 'location'=>'payments', 'folder'=>'payments']);
        \Solunes\Master\App\Node::create(['name'=>'online-transaction-item', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_online_transaction->id]);
        \Solunes\Master\App\Node::create(['name'=>'online-transaction-shipping', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_online_transaction->id]);
        \Solunes\Master\App\Node::create(['name'=>'online-transaction-payment', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_online_transaction->id]);
        $node_online_bank = \Solunes\Master\App\Node::create(['name'=>'online-bank', 'location'=>'payments', 'folder'=>'payments']);
        \Solunes\Master\App\Node::create(['name'=>'online-bank-deposit', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_online_bank->id]);

        // Crear Métodos de Pago por Defecto
        \Solunes\Sales\App\PaymentMethod::create(['name'=>'Transferencia Bancaria', 'code'=>'bank-deposit', 'model'=>'BankDeposit', 'description'=>'<p>Realiza una transferencia bancaria a:</p>', 'automatic'=>0]);
        \Solunes\Sales\App\PaymentMethod::create(['name'=>'PagosTT', 'code'=>'pagostt', 'model'=>'Pagostt', 'description'=>'<p>Realiza una transferencia bancaria a:</p>']);
        \Solunes\Sales\App\PaymentMethod::create(['name'=>'PayPal', 'code'=>'paypal', 'model'=>'Paypal', 'description'=>'<p>Realiza una transferencia bancaria a:</p>', 'recurrent_payments'=>1]);
        \Solunes\Sales\App\PaymentMethod::create(['name'=>'PayMe', 'code'=>'payme', 'model'=>'Payme', 'description'=>'<p>Realiza una transferencia bancaria a:</p>', 'active'=>0]);
        \Solunes\Sales\App\PaymentMethod::create(['name'=>'Tigo Money', 'code'=>'tigo-money', 'model'=>'TigoMoney', 'description'=>'<p>Realiza una transferencia bancaria a:</p>', 'active'=>0]);
        \Solunes\Sales\App\PaymentMethod::create(['name'=>'PagosNet', 'code'=>'pagosnet', 'model'=>'PagosNet', 'description'=>'<p>Realiza una transferencia bancaria a:</p>', 'active'=>0]);

        // Usuarios
        $admin = \Solunes\Master\App\Role::where('name', 'admin')->first();
        $member = \Solunes\Master\App\Role::where('name', 'member')->first();
        if(!\Solunes\Master\App\Permission::where('name','payments')->first()){
            $payments_perm = \Solunes\Master\App\Permission::create(['name'=>'payments', 'display_name'=>'Pagos']);
            $admin->permission_role()->attach([$payments_perm->id]);
        }

    }
}