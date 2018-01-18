<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class ScheduledTransaction extends Model {
	
	protected $table = 'scheduled_transactions';
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
    
    public function scheduled_transaction_items() {
        return $this->hasMany('Solunes\Payments\App\ScheduledTransactionItem', 'parent_id');
    }
        
    public function scheduled_transaction_payments() {
        return $this->hasMany('Solunes\Payments\App\ScheduledTransactionPayment', 'parent_id');
    }
    
}