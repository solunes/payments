<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class TransactionPayment extends Model {
	
	protected $table = 'transaction_payments';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'parent_id'=>'required',
		'payment_id'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'parent_id'=>'required',
		'payment_id'=>'required',
	);
    
    public function transaction() {
        return $this->belongsTo('Solunes\Payments\App\Transaction', 'parent_id');
    }
        
    public function parent() {
        return $this->belongsTo('Solunes\Payments\App\Transaction');
    }

    public function payment() {
        return $this->belongsTo('Solunes\Payments\App\Payment');
    }

}