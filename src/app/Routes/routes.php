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
    Route::get('/finish-sale-payment/{sale_id}/{type}', 'ProcessController@getFinishSalePayment');
});

Route::group(['prefix'=>'pagostt'], function(){
    Route::get('/make-all-payments/{customer_id}/{custom_app_key?}', 'PagosttController@getMakeAllPayments');
    Route::get('/make-single-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PagosttController@getMakeSinglePayment');
    Route::post('/make-checkbox-payment', 'PagosttController@postMakeCheckboxPayment');
});