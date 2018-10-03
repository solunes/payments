<?php

return [

	// ACTIVE PAYMENT METHODS
	'manual' => true,
	'bank-deposit' => false,
	'pagostt' => true,
	'paypal' => false,
	'payme' => false,
	'tigo-money' => false,
	'pagosnet' => false,

    'pagostt_params' => [
		'testing' => false, // Utilizar el ambiente de pruebas
		'main_server' => 'http://www.todotix.com:10365/rest/', // URL DE PagosTT para Producción
		'test_server' => 'http://www.todotix.com:10888/rest/', // URL DE PagosTT para Pruebas
		'salt' => 'GfFJo519zBd7gzmIBhNd0vBK2Co375bS', // Llave de encriptación, reemplazar por la del proyecto
		'secret_iv' => '!IV@_$2', // Secret IV de encriptación, reemplazar por oficial de cuentas 365
		'secret_pagostt_iv' => '!#$a54?3', // Secret IV de encriptación de PAgosTT, integrado con Beto
		'app_name' => env('APP_NAME', 'PagosTT'), // Nombre enviado a Cuentas365
		'app_key' => '189e565f-907e-4775-9d9c-17097e55aaa4', // AppKey generado por PagosTT
		'test_app_key' => 'A63740BF-878D-4D2F-A324-D2DD50772D4D', // AppKey generado por PagosTT
		'custom_app_keys' => ['default'=>'189e565f-907e-4775-9d9c-17097e55aaa4'], // AppKey personalizados para ser utilizados
		'custom_test_app_keys' => ['default'=>'A63740BF-878D-4D2F-A324-D2DD50772D4D'], // AppKey personalizados para ser utilizados en modo Testing
		'invoice_server' => 'http://www.todotix.com:7777/factura/', // Servidor donde se almacenan las facturas, pegado al Invoice ID
		'invoice_test_server' => 'http://todotix.com:20888/factura/', // Servidor donde se almacenan las facturas en pruebas, pegado al Invoice ID
		'notify_email' => true, // Notificar la recepción del pago por correo electrónico
		'enable_cashier' => false, // Definir si se habilita el pago en caja
		'cashier_payments' => ['default'=>NULL], // Definir la llave de pagos en caja de PagosTT para la compañia
		'test_cashier_payments' => ['default'=>'e93a676e14f672667ab6f7fe863061822331b13b258afd8cb14e0e5cd46c6ff1'], // Definir la llave de pagos en caja de PagosTT para la compañia en Modo Testing
		'enable_bridge' => false, // Habilitar si no se utilizarán los módulos de pagos de Solunes
		'enable_cycle' => false, // Habilitar la facturación por ciclos
		'enable_preinvoice' => false, // Habilitar la generación de prefacturas
		'finish_payment_verification' => false, // Habilitar si se desea realizar la verificación final
		'customer_all_payments' => true, // Habilitar si se desea aceptar pagos en masa
		'customer_recurrent_payments' => false, // Habilitar si se desea integrar a Cuentas365
		'is_cuentas365' => false, // Habilitar la plataforma es Cuentas365
	],

	// PARAMETROS
	'scheduled_transactions' => false,
	'invoices' => true,
	'shipping' => false,
	'online_banks' => false,

	// SEGURIDAD Y ENCRIPTACION
	'salt' => 'GfFJo519zBd7gzmIBhNd0vBK2Co375bS', // Llave de encriptación, reemplazar por la del proyecto
	'secret_iv' => '!IV@_$2', // Secret IV de encriptación, reemplazar por oficial de cuentas 365
	
	// PAGOSTT
	'pagostt_app_key' => 'c26d8c99-8836-4cd5-a850-230c9d3fbf3c', // AppKey generado por PagosTT
	'paypal_app_key' => 'c26d8c99-8836-4cd5-a850-230c9d3fbf3c', // AppKey generado por PagosTT

];