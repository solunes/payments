<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {
	
	protected $table = 'transactions';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'callback_url'=>'required',
		'payment_code'=>'required',
		'payment_method_id'=>'required',
		'processed'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'callback_url'=>'required',
		'payment_code'=>'required',
		'payment_method_id'=>'required',
		'processed'=>'required',
	);
    
    public function payment_method() {
        return $this->belongsTo('Solunes\Payments\App\PaymentMethod');
    }
    
    public function transaction_payments() {
        return $this->hasMany('Solunes\Payments\App\TransactionPayment', 'parent_id');
    }

}