<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class PaymentInvoice extends Model {
	
	protected $table = 'payment_invoices';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'invoice_type'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'invoice_type'=>'required',
	);
    
    public function payment() {
        return $this->belongsTo('Solunes\Payments\App\Payment', 'parent_id');
    }
    
    public function parent() {
        return $this->belongsTo('Solunes\Payments\App\Payment');
    }
    
}