<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
	
	protected $table = 'payments';
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
    
    public function payment_items() {
        return $this->hasMany('Solunes\Payments\App\PaymentItem', 'parent_id');
    }
    
    public function payment_shippings() {
        return $this->hasMany('Solunes\Payments\App\PaymentShipping', 'parent_id');
    }
    
    public function payment_transactions() {
        return $this->hasMany('Solunes\Payments\App\PaymentTransaction', 'parent_id');
    }

}