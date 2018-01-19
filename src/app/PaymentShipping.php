<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class PaymentShipping extends Model {
	
	protected $table = 'payment_shippings';
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
    
    public function payment() {
        return $this->belongsTo('Solunes\Payments\App\Payment', 'parent_id');
    }

}