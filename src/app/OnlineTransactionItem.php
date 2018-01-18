<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class OnlineTransactionItem extends Model {
	
	protected $table = 'online_transaction_items';
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
    
    public function online_transaction() {
        return $this->belongsTo('Solunes\Payments\App\OnlineTransaction', 'parent_id');
    }

}