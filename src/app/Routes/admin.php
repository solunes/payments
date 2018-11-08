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
    Route::get('/make-cashier-payment/{customer_id}/{payment_id}', 'ProcessController@getMakeManualCashierPayment');
    Route::post('/make-checkbox-payment', 'ProcessController@postMakeCheckboxPayment');
});

Route::group(['prefix'=>'payme'], function(){
    Route::get('/payment-iframe/{payment_code}', 'PaymeController@getPaymentIframe');
    Route::get('/transaction-from-payme/{payment_code}', 'PaymeController@getTransactionFromPayme');
    Route::get('/make-all-payments/{customer_id}/{custom_app_key?}', 'PaymeController@getMakeAllPayments');
    Route::get('/make-single-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PaymeController@getMakeSinglePayment');
    Route::post('/make-checkbox-payment', 'PaymeController@postMakeCheckboxPayment');
});

Route::group(['prefix'=>'test'], function(){
    Route::get('/encryption/{text}', 'TestController@getEncryptionTest');
    Route::get('/decryption/{text}', 'TestController@getDecryptionTest');
});