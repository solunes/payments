<?php

namespace Solunes\Payments\App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model {
    
    protected $table = 'payments';
    protected $appends = array('amount','can_pay','can_pay_two');
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
        
    public function scopeCheckOwner($query, $customer_ids = NULL) {
        if(is_array($customer_ids)&&count($customer_ids)>0){
            //$customer_ids = $customer_ids;
        } else if(\Auth::check()){
            $customer_ids = \Auth::user()->customers()->lists('id')->toArray();
        } else {
            $customer_ids = [0];
        }
        return $query->whereIn('customer_id', $customer_ids);
    }

    public function currency() {
        return $this->belongsTo('Solunes\Business\App\Currency');
    }
    
    public function agency() {
        return $this->belongsTo('Solunes\Business\App\Agency');
    }
        
    public function company() {
        return $this->belongsTo('Solunes\Business\App\Company');
    }
                
    public function customer_payment() {
        return $this->belongsTo('Solunes\Customer\App\CustomerPayment');
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

    public function payment_check_inverse() {
        return $this->hasOne('Solunes\Payments\App\Payment', 'payment_check_id', 'id');
    }

    public function payment_check() {
        return $this->hasOne('Solunes\Payments\App\Payment', 'id', 'payment_check_id');
    }
    
    public function getCanPayAttribute() {
        if($this->message_block||$this->payment_check){
            return false;
        }
        return true;
    }
    
    public function getCanPayTwoAttribute() {
        if($this->message_block||$this->payment_check){
            return false;
        }
        return true;
    }

    public function getAmountAttribute() {
        $total = 0;
        foreach($this->payment_items as $item){
            $total += $item->amount;
        }
        return $total;
    }

}