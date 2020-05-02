<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['prefix'=>'payments'], function(){
    Route::get('/make-all-payments/{customer_id}', 'ProcessController@getMakeAllPayments');
    Route::get('/make-single-payment/{customer_id}/{payment_id}', 'ProcessController@getMakeSinglePayment');
    Route::get('/payment/{payment_id}', 'ProcessController@getPayment');
    Route::get('/cancel-payment/{payment_id}', 'ProcessController@getCancelPayment');
    Route::get('/finish-sale-payment/{sale_id}/{type}', 'ProcessController@getFinishSalePayment');
});

Route::group(['prefix'=>'pagostt'], function(){
    Route::get('/create-custom-payment/{customer_id}', 'PagosttController@getCreateCustomPayment');
    Route::post('/create-custom-payment', 'PagosttController@postCreateCustomPayment');
    Route::get('/make-all-payments/{customer_id}/{custom_app_key?}', 'PagosttController@getMakeAllPayments');
    Route::get('/make-manual-cashier-customer-all-payments/{customer_id}/{custom_app_key?}', 'PagosttController@getMakeManualCashierCustomerAllPayments');
    Route::get('/make-single-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PagosttController@getMakeSinglePayment');
    Route::get('/make-manual-cashier-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PagosttController@getMakeManualCashierPayment');
    Route::post('/make-checkbox-payment', 'PagosttController@postMakeCheckboxPayment');
    Route::post('/make-checkbox-manual-cashier-payment', 'PagosttController@postMakeCheckboxManualCashierPayment');
});

Route::group(['prefix'=>'paypal'], function(){
    Route::get('/make-all-payments/{customer_id}/{custom_app_key?}', 'PaypalPaymentController@getMakeAllPayments');
    Route::get('/make-single-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PaypalPaymentController@getMakeSinglePayment');
    Route::post('/make-checkbox-payment', 'PaypalPaymentController@postMakeCheckboxPayment');
});

Route::group(['prefix'=>'payu'], function(){
    Route::get('/make-all-payments/{customer_id}/{custom_app_key?}', 'PaypalPaymentController@getMakeAllPayments');
    Route::get('/make-single-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PaypalPaymentController@getMakeSinglePayment');
    Route::post('/make-checkbox-payment', 'PaypalPaymentController@postMakeCheckboxPayment');
});

Route::group(['prefix'=>'neteller'], function(){
    Route::get('/make-all-payments/{customer_id}/{custom_app_key?}', 'PaypalPaymentController@getMakeAllPayments');
    Route::get('/make-single-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PaypalPaymentController@getMakeSinglePayment');
    Route::post('/make-checkbox-payment', 'PaypalPaymentController@postMakeCheckboxPayment');
});

Route::group(['prefix'=>'process'], function(){
    Route::post('/bank-deposit', 'ProcessController@postBankDeposit')->middleware('auth');
    Route::post('/cash-payment', 'ProcessController@postCashPayment')->middleware('auth');
});