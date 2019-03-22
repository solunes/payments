<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class PaymentInvoice extends Model {
	
	protected $table = 'payment_invoices';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'parent_id'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'parent_id'=>'required',
	);
            
    public function parent() {
        return $this->belongsTo('Solunes\Payments\App\Payment');
    }

    public function payment() {
        return $this->belongsTo('Solunes\Payments\App\Payment', 'parent_id');
    }

}