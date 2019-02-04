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
        'real_amount'=>'required',
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
        'real_amount'=>'required',
        'status'=>'required',
        'active'=>'required',
    );
    
    public function scopeFindId($query, $id) {
        return $query->where('id', $id);
    }

    public function scopeStatus($query, $status) {
        return $query->where('status', $status);
    }
        
    public function scopeCheckOwner($query, $customer_id) {
        if(\Auth::check()){
            $user_id = \Auth::user()->customers()->lists('id')->toArray();
        } else {
            $user_id = 0;
        }
        return $query->whereIn('customer_id', $customer_id);
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }
    
    public function company() {
        return $this->belongsTo('Solunes\Business\App\Company');
    }
        
    public function sale_payment() {
        return $this->hasOne('Solunes\Sales\App\SalePayment');
    }

    public function customer() {
        if(config('solunes.todotix-customer')){
            return $this->belongsTo('Todotix\Customer\App\Customer');
        } else if(config('solunes.customer')){
            return $this->belongsTo('Solunes\Customer\App\Customer');
        } else {
            return $this->belongsTo('App\Customer');
        }
    }

    public function cashier_user() {
        return $this->belongsTo('App\User');
    }
        
    public function payment_item() {
        return $this->hasOne('Solunes\Payments\App\PaymentItem', 'parent_id');
    }
        
    public function payment_items() {
        return $this->hasMany('Solunes\Payments\App\PaymentItem', 'parent_id');
    }
        
    public function payment_shipping() {
        return $this->hasOne('Solunes\Payments\App\PaymentShipping', 'parent_id');
    }

    public function payment_shippings() {
        return $this->hasMany('Solunes\Payments\App\PaymentShipping', 'parent_id');
    }
        
    public function payment_invoices() {
        return $this->hasMany('Solunes\Payments\App\PaymentInvoice', 'parent_id');
    }
        
    public function payment_invoice() {
        return $this->hasOne('Solunes\Payments\App\PaymentInvoice', 'parent_id');
    }
    
    public function transaction_payments() {
        return $this->hasMany('Solunes\Payments\App\TransactionPayment');
    }
        
    public function processed_transaction_payments() {
        return $this->hasMany('Solunes\Payments\App\TransactionPayment')->where('processed', 1);
    }

    public function getAmountAttribute() {
        $total = 0;
        foreach($this->payment_items as $item){
            $total += $item->amount;
        }
        return $total;
    }

}