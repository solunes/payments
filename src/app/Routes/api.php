<?php

app('api.router')->group(['version'=>'v1', 'namespace'=>'Solunes\\Payments\\App\\Controllers\\Api'], function($api){
	$api->get('pagos-de-cliente/{app_token}/{customer_id}/{transaction_id?}', 'PagosttController@getCustomerPayments');
	$api->get('payme-pago-confirmado/{payment_code}', 'PaymeController@getSuccessfulPayment');
	$api->get('pago-confirmado/{payment_code}/{transaction_id?}', 'PagosttController@getSuccessfulPayment');
	$api->get('confirmed-payment/{payment_code}/{transaction_id?}', 'PaymentsController@getSuccessfulPayment');
});