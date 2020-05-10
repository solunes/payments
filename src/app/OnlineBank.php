<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class OnlineBank extends Model {
	
	protected $table = 'online_banks';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'name'=>'required',
		'account_number'=>'required',
		'currency_id'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
		'name'=>'required',
		'account_number'=>'required',
		'currency_id'=>'required',
	);
        
    public function agency() {
        return $this->belongsTo('Solunes\Business\App\Agency');
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }

    public function online_bank_deposits() {
        return $this->hasMany('Solunes\Payments\App\OnlineBankDeposit', 'parent_id');
    }
        
    public function getFullNameAttribute() {
        return $this->name.' - '.$this->account_number;
    }

}