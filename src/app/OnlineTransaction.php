<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class OnlineTransaction extends Model {
	
	protected $table = 'online_transactions';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'customer_id'=>'required',
		'payment_code'=>'required',
		'transaction_id'=>'required',
		'amount'=>'required',
		'status'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
		'customer_id'=>'required',
		'payment_code'=>'required',
		'transaction_id'=>'required',
		'amount'=>'required',
		'status'=>'required',
	);
    
    public function online_transaction_items() {
        return $this->hasMany('Solunes\Payments\App\OnlineTransactionItem', 'parent_id');
    }
    
    public function online_transaction_shippings() {
        return $this->hasMany('Solunes\Payments\App\OnlineTransactionShipping', 'parent_id');
    }
    
    public function online_transaction_payments() {
        return $this->hasMany('Solunes\Payments\App\OnlineTransactionPayment', 'parent_id');
    }

}