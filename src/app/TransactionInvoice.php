<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class TransactionInvoice extends Model {
	
	protected $table = 'transaction_invoices';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'parent_id'=>'required',
		'invoice_type'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'parent_id'=>'required',
		'invoice_type'=>'required',
	);
    
    public function parent() {
        return $this->belongsTo('Solunes\Payments\App\Transaction');
    }
        
    public function transaction() {
        return $this->belongsTo('Solunes\Payments\App\Transaction', 'parent_id');
    }

}