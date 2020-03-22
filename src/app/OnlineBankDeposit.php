<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class OnlineBankDeposit extends Model {
	
	protected $table = 'online_bank_deposits';
	public $timestamps = true;
	
	/* Sending rules */
	public static $rules_send = array(
		'online_bank_id'=>'required',
		'sale_payment_id'=>'required',
		'image'=>'required|file',
	);

	/* Creating rules */
	public static $rules_create = array(
		'sale_payment_id'=>'required',
		'transaction_id'=>'required',
		'image'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'sale_payment_id'=>'required',
		'transaction_id'=>'required',
		'image'=>'required',
	);
        
    public function parent() {
        return $this->belongsTo('Solunes\Payments\App\OnlineBank');
    }

    public function online_bank() {
        return $this->belongsTo('Solunes\Payments\App\OnlineBank', 'parent_id');
    }

}