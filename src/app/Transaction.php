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
		'status'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'callback_url'=>'required',
		'payment_code'=>'required',
		'payment_method_id'=>'required',
		'status'=>'required',
	);
        
    public function customer() {
        if(config('solunes.todotix-customer')){
            return $this->belongsTo('Todotix\Customer\App\Customer');
        } else if(config('solunes.customer')){
            return $this->belongsTo('Solunes\Customer\App\Customer');
        } else {
            return $this->belongsTo('App\Customer');
        }
    }

    public function payment_method() {
        return $this->belongsTo('Solunes\Payments\App\PaymentMethod');
    }
    
    public function transaction_payments() {
        return $this->hasMany('Solunes\Payments\App\TransactionPayment', 'parent_id');
    }

    public function transaction_invoices() {
        return $this->hasMany('Solunes\Payments\App\TransactionInvoice', 'parent_id');
    }

    public function transaction_invoice() {
        return $this->hasOne('Solunes\Payments\App\TransactionInvoice', 'parent_id');
    }

}