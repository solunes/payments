<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    
    protected $table = 'payments';
    public $timestamps = true;

    /* Creating rules */
    public static $rules_create = array(
        'currency_id'=>'required',
        'name'=>'required',
        'date'=>'required',
        'invoice'=>'required',
        'amount'=>'required',
        'status'=>'required',
        'active'=>'required',
    );

    /* Updating rules */
    public static $rules_edit = array(
        'id'=>'required',
        'currency_id'=>'required',
        'name'=>'required',
        'date'=>'required',
        'invoice'=>'required',
        'amount'=>'required',
        'status'=>'required',
        'active'=>'required',
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
    
    public function customer() {
        return $this->belongsTo('Todotix\Customer\App\Customer');
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
    
    public function getAmountAttribute() {
        $total = 0;
        foreach($this->payment_items as $item){
            $total += $item->amount;
        }
        return $total;
    }

}