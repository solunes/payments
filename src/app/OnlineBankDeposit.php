<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class OnlineBankDeposit extends Model {
	
	protected $table = 'online_bank_deposits';
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
        return $this->belongsTo('Solunes\Payments\App\OnlineBank');
    }

    public function online_bank() {
        return $this->belongsTo('Solunes\Payments\App\OnlineBank', 'parent_id');
    }

}