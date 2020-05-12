<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model {
	
	protected $table = 'payment_methods';
	public $timestamps = true;

	/* Creating rules */
	public static $rules_create = array(
		'name'=>'required',
		'code'=>'required',
		'model'=>'required',
		'active'=>'required',
	);

	/* Updating rules */
	public static $rules_edit = array(
		'id'=>'required',
		'name'=>'required',
		'code'=>'required',
		'model'=>'required',
		'active'=>'required',
	);

    public function scopeActive($query) {
        return $query->where('active', 1);
    }

    public function scopeInactive($query) {
        return $query->where('active', 0);
    }

    public function scopeOrder($query) {
        return $query->orderBy('id', 'ASC');
    }

}