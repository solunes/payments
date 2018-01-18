<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class OnlineBank extends Model {
	
	protected $table = 'online_banks';
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
    
    public function online_bank_deposits() {
        return $this->hasMany('Solunes\Payments\App\OnlineBankDeposit', 'parent_id');
    }
    
}