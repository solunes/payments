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
        $node_scheduled_transaction = \Solunes\Master\App\Node::create(['name'=>'scheduled-transaction', 'location'=>'payments', 'folder'=>'payments']);
        \Solunes\Master\App\Node::create(['name'=>'scheduled-transaction-item', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_scheduled_transaction->id]);
        \Solunes\Master\App\Node::create(['name'=>'scheduled-transaction-payment', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_scheduled_transaction->id]);
        $node_online_transaction = \Solunes\Master\App\Node::create(['name'=>'online-transaction', 'location'=>'payments', 'folder'=>'payments']);
        \Solunes\Master\App\Node::create(['name'=>'online-transaction-item', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_online_transaction->id]);
        \Solunes\Master\App\Node::create(['name'=>'online-transaction-shipping', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_online_transaction->id]);
        \Solunes\Master\App\Node::create(['name'=>'online-transaction-payment', 'location'=>'payments', 'folder'=>'payments', 'type'=>'subchild', 'parent_id'=>$node_online_transaction->id]);

        // Usuarios
        $admin = \Solunes\Master\App\Role::where('name', 'admin')->first();
        $member = \Solunes\Master\App\Role::where('name', 'member')->first();
        if(!\Solunes\Master\App\Permission::where('name','payments')->first()){
            $payments_perm = \Solunes\Master\App\Permission::create(['name'=>'payments', 'display_name'=>'Pagos']);
            $admin->permission_role()->attach([$payments_perm->id]);
        }

    }
}