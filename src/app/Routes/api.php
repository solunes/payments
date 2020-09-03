<?php

app('api.router')->group(['version'=>'v1', 'namespace'=>'Solunes\\Payments\\App\\Controllers\\Api'], function($api){
	$api->get('pagos-de-cliente/{app_token}/{customer_id}/{transaction_id?}', 'PagosttController@getCustomerPayments');
	$api->get('payme-pago-confirmado/{payment_code}', 'PaymeController@getSuccessfulPayment');
	$api->get('pago-confirmado/{payment_code}/{transaction_id?}', 'PagosttController@getSuccessfulPayment');
	$api->get('pagatodo-redireccion/{status}/{transaction_id}', 'PagatodoController@getSuccessfulPayment');
	$api->post('pagatodo-callback', 'PagatodoController@postSuccessfulPayment');
	$api->get('confirmed-payment/{payment_code}/{transaction_id?}', 'PaymentsController@getSuccessfulPayment');
	$api->get('paypal-success/{payment_code}/{transaction_id?}', 'PaypalController@getSuccessfulPayment');
});