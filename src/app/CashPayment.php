<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class CashPayment extends Model {
	
	protected $table = 'cash_payments';
	public $timestamps = true;
	
	/* Sending rules */
	public static $rules_send = array(
		'sale_payment_id'=>'required',
		'amount'=>'required',
	);

	/* Creating rules */
	public static $rules_create = array(
		'payment_transaction_id'=>'required',
		'transaction_id'=>'required',
		'amount'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
		'payment_transaction_id'=>'required',
		'transaction_id'=>'required',
		'amount'=>'required',
	);
    
}