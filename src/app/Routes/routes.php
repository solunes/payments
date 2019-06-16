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
    Route::get('/create-custom-payment/{customer_id}', 'PagosttController@getCreateCustomPayment');
    Route::post('/create-custom-payment', 'PagosttController@postCreateCustomPayment');
    Route::get('/make-all-payments/{customer_id}/{custom_app_key?}', 'PagosttController@getMakeAllPayments');
    Route::get('/make-single-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PagosttController@getMakeSinglePayment');
    Route::get('/make-manual-cashier-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PagosttController@getMakeManualCashierPayment');
    Route::post('/make-checkbox-payment', 'PagosttController@postMakeCheckboxPayment');
});

Route::group(['prefix'=>'paypal'], function(){
    Route::get('/make-all-payments/{customer_id}/{custom_app_key?}', 'PaypalPaymentController@getMakeAllPayments');
    Route::get('/make-single-payment/{customer_id}/{payment_id}/{custom_app_key?}', 'PaypalPaymentController@getMakeSinglePayment');
    Route::post('/make-checkbox-payment', 'PaypalPaymentController@postMakeCheckboxPayment');
    Route::get('/make-paypal', 'PaypalPaymentController@paywithPaypal');
    Route::get('/make-credit-card', 'PaypalPaymentController@paywithCreditCard');
    Route::get('/test-token', 'Api\PaypalController@getToken');
    Route::get('/test-checkout', 'Api\PaypalController@getCheckoutOrder');
    Route::get('/test-checkout-order/{code}', 'Api\PaypalController@getCheckoutOrderItem');
    Route::get('/test-checkout-order-capture/{code}', 'Api\PaypalController@getCheckoutOrderItemCapture');
});
