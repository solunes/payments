<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    
    protected $table = 'payments';
    public $timestamps = true;

    /* Creating rules */
    public static $rules_create = array(
        'customer_id'=>'required',
        'payment_code'=>'required',
        'transaction_id'=>'required',
        'amount'=>'required',
        'status'=>'required',
    );

    /* Updating rules */
    public static $rules_edit = array(
        'id'=>'required',
        'customer_id'=>'required',
        'payment_code'=>'required',
        'transaction_id'=>'required',
        'amount'=>'required',
        'status'=>'required',
    );
    
    public function scopeFindId($query, $id) {
        return $query->where('id', $id);
    }

    public function scopeStatus($query, $status) {
        return $query->where('status', $status);
    }
        
    public function scopeCheckOwner($query) {
        if(\Auth::check()){
            $user_id = \Auth::user()->id;
        } else {
            $user_id = 0;
        }
        return $query->where('user_id', $user_id);
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }
    
    public function company() {
        return $this->belongsTo('Solunes\Business\App\Company');
    }

    public function payment_items() {
        return $this->hasMany('Solunes\Payments\App\PaymentItem', 'parent_id');
    }
    
    public function payment_shippings() {
        return $this->hasMany('Solunes\Payments\App\PaymentShipping', 'parent_id');
    }
    
    public function payment_transactions() {
        return $this->hasMany('Solunes\Payments\App\PaymentTransaction', 'parent_id');
    }

}