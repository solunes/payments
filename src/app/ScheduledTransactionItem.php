<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class ScheduledTransactionItem extends Model {
	
	protected $table = 'scheduled_transaction_items';
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
    
    public function parent() {
        return $this->belongsTo('Solunes\Payments\App\ScheduledTransaction');
    }
    
    public function scheduled_transaction() {
        return $this->belongsTo('Solunes\Payments\App\ScheduledTransaction', 'parent_id');
    }

}