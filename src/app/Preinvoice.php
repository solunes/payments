<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class Preinvoice extends Model {
	
	protected $table = 'preinvoices';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'payment_id'=>'required',
		'nit_name'=>'required',
		'nit_number'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
		'payment_id'=>'required',
		'nit_name'=>'required',
		'nit_number'=>'required',
	);
    
    public function preinvoice_items() {
        return $this->hasMany('Solunes\Payments\App\PreinvoiceItem', 'parent_id');
    }
    
}