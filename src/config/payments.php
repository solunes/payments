<?php

return [

	// ACTIVE PAYMENT METHODS
	'bank-deposit' => false,
	'pagostt' => true,
	'paypal' => false,
	'payme' => false,
	'tigo-money' => false,
	'pagosnet' => false,

	// PARAMETROS
	'scheduled_transactions' => false,
	'shipping' => false,
	'online_banks' => false,

	// SEGURIDAD Y ENCRIPTACION
	'salt' => 'GfFJo519zBd7gzmIBhNd0vBK2Co375bS', // Llave de encriptación, reemplazar por la del proyecto
	'secret_iv' => '!IV@_$2', // Secret IV de encriptación, reemplazar por oficial de cuentas 365
	
	// PAGOSTT
	'pagostt_app_key' => 'c26d8c99-8836-4cd5-a850-230c9d3fbf3c', // AppKey generado por PagosTT
	'paypal_app_key' => 'c26d8c99-8836-4cd5-a850-230c9d3fbf3c', // AppKey generado por PagosTT

];