<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model {
	
	protected $table = 'payment_items';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'currency_id'=>'required',
		'name'=>'required',
		'price'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'currency_id'=>'required',
		'name'=>'required',
		'price'=>'required',
	);
        
    public function parent() {
        return $this->belongsTo('Solunes\Payments\App\Payment');
    }

    public function payment() {
        return $this->belongsTo('Solunes\Payments\App\Payment', 'parent_id');
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }

    public function getAmountAttribute() {
        return ($this->price * $this->quantity) + $this->tax;
    }

}