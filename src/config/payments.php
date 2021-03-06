<?php

return [

	'redirect_after_payment' => 'account/my-payments/456452332',
	'redirect_after_payment_error' => 'account/my-payments/456452332',
	'sfv_version' => 1,
	'discounts' => false,
	'receipts' => false,
	'default_payment_method_code' => 'pagostt',
	'notify_agency_on_payment' => false,
	'open_custom_app_keys' => false,
	'customer_cancel_payments' => false,
	'custom_customer_cancel_payments' => false,

	// ACTIVE PAYMENT METHODS
	'manual' => true,
	'cash' => false,
	'bank-deposit' => false,
	'pagostt' => true,
	'paypal' => false,
	'payme' => false,
	'payu' => false,
	'neteller' => false,
	'tigo-money' => false,
	'pagosnet' => false,
	'pagatodo' => false,
	'banipay' => false,
	'test-payment' => false,

    'pagostt_params' => [
		'testing' => env('ENABLE_TEST', 1), // Utilizar el ambiente de pruebas
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
		'use_customer_invoice_data' => false, // Definir true cuando se utilicen los datos del cliente sobre los datos del payment.
		'cashier_payments' => ['default'=>NULL], // Definir la llave de pagos en caja de PagosTT para la compañia
		'test_cashier_payments' => ['default'=>'e93a676e14f672667ab6f7fe863061822331b13b258afd8cb14e0e5cd46c6ff1'], // Definir la llave de pagos en caja de PagosTT para la compañia en Modo Testing
		'enable_custom_func' => false, // Habilitar si se habilita el CustomFunc
		'enable_bridge' => false, // Habilitar si no se utilizarán los módulos de pagos de Solunes
		'enable_cycle' => false, // Habilitar la facturación por ciclos
		'enable_preinvoice' => false, // Habilitar la generación de prefacturas
		'finish_payment_verification' => false, // Habilitar si se desea realizar la verificación final
		'customer_all_payments' => true, // Habilitar si se desea aceptar pagos en masa
		'customer_recurrent_payments' => false, // Habilitar si se desea integrar a Cuentas365
		'is_cuentas365' => false, // Habilitar la plataforma es Cuentas365
	],

    'payme_params' => [
		'testing' => env('ENABLE_TEST', 1), // Utilizar el ambiente de pruebas
		'min_amount' => false, // Utilizar el ambiente de pruebas para transacciones de 1 Bs
		'main_server' => 'https://vpayment.verifika.com/', // URL DE PagosTT para Producción
		'test_server' => 'https://integracion.alignetsac.com/', // URL DE PagosTT para Pruebas
		'sha_key_production' => 'cuhceSEkyTVvnbqHSc_95627234825', // ID Adquiriente de Payme Enlace Producción
		'sha_key_testing' => 'cuhceSEkyTVvnbqHSc_95627234825', // ID Adquiriente de Payme Enlace Testing
		'shawallet_key_production' => 'sSkzcykQEmnVuqEEGXZ_327276446743', // ID Adquiriente de Payme Enlace Producción
		'shawallet_key_testing' => 'KPeRTLezvfGKUPAsWTH$688688542255', // ID Adquiriente de Payme Enlace Testing
		'commerce_id_production' => '8056', // ID Comercio de Payme Enlace Producción
		'commerce_id_testing' => '8056', // ID Comercio de Payme Enlace Testing
		'acquirer_id_production' => '99', // ID Adquiriente de Payme Enlace Producción
		'acquirer_id_testing' => '99', // ID Adquiriente de Payme Enlace Testing
		'idEntCommerce_production' => '1926', // ID Adquiriente de Payme Enlace Producción
		'idEntCommerce_testing' => '1926', // ID Adquiriente de Payme Enlace Testing
		'enable_wallet' => false, // Habilitar si se desea utilizar el wallet de Payme
		'iso_currency_code' => '068', // Codigo ISO de Moneda
		'design_option' => '1', // Opciones de diseño de iframe 1, 2 o 3
		'app_name' => env('APP_NAME', 'PagosTT'), // Nombre enviado a Cuentas365
		'enable_bridge' => false, // Habilitar si no se utilizarán los módulos de pagos de Solunes
		'finish_payment_verification' => false, // Habilitar si se desea realizar la verificación final
	],

    'paypal_params' => [
		'testing' => env('ENABLE_TEST', 1), // Utilizar el ambiente de pruebas
		'live_api_account' => 'bernardapelaez_api1.gmail.com', // Usuario de PayPal Live
		'live_api_client' => '6HEATXCAZ9RUXC94', // Password de PayPal Live
		'live_api_secret' => 'AaJHGLAdDj9X3X5RoVTTz4UWvpzEAVFCZSzrpljlYfsJtYzXvPUrqIGl', // Signature de PayPal Live
		'live_access_token' => false, // Acccess Token de PayPal Live
		'sandbox_api_account' => 'edumejia30-facilitator@gmail.com', // Account de PayPal Sandbox
		'sandbox_api_client' => 'AbDLG2VusaA4UUZ-8S4k1dB-vcCwfQSpVbD_2tIEYxMSUUQiQRKe_HniUfj7kf53q-qlIDh8Y_y-ElXa', // Client ID de PayPal Sandbox
		'sandbox_api_secret' => 'ENiz9hQCUgLNBguv4c_vm10B-irgzVJL6GkykEWOEfFESth-ExQZ1nMpNNmTDV_acxtTdnxlbNMB1rHF', // Secret de PayPal Sandbox
		'sandbox_access_token' => false, // Acccess Token de PayPal Sandbox
		'app_name' => env('APP_NAME', 'PayPal'), // Nombre enviado a Cuentas365
		'enable_bridge' => false, // Habilitar si no se utilizarán los módulos de pagos de Solunes
		'finish_payment_verification' => false, // Habilitar si se desea realizar la verificación final
	],

    'pagatodo_params' => [
		'testing' => env('ENABLE_TEST', 1), // Utilizar el ambiente de pruebas
		'main_server' => 'https://lyon.pagatodo360.net:4000/', // URL DE PagosTT para Producción
		'test_server' => 'https://lyon.pagatodo360.net:4000/', // URL DE PagosTT para Pruebas
		'user' => 'test', // AppKey generado por PagosTT
		'pass' => 'test', // AppKey generado por PagosTT
		'test_user' => 'test', // AppKey generado por PagosTT
		'test_pass' => 'test', // AppKey generado por PagosTT
		'custom_users' => ['default'=>'test'], // AppKey personalizados para ser utilizados
		'custom_test_users' => ['default'=>'test'], // AppKey personalizados para ser utilizados en modo Testing
		'custom_passwords' => ['default'=>'test'], // AppKey personalizados para ser utilizados
		'custom_test_passwords' => ['default'=>'test'], // AppKey personalizados para ser utilizados en 
		'idempresa' => 31, // Notificar la recepción del pago por correo electrónico
		'idempresa_test' => 31, // Notificar la recepción del pago por correo electrónico
		'notify_email' => true, // Notificar la recepción del pago por correo electrónico
		'enable_custom_func' => false, // Habilitar si se habilita el CustomFunc
		'enable_bridge' => false, // Habilitar si no se utilizarán los módulos de pagos de Solunes
		'finish_payment_verification' => false, // Habilitar si se desea realizar la verificación final
		'customer_all_payments' => true, // Habilitar si se desea aceptar pagos en masa
	],

    'banipay_params' => [
		'testing' => env('ENABLE_TEST', 1), // Utilizar el ambiente de pruebas
		'main_server' => 'https://banipay.me:8443/api/', // URL DE PagosTT para Producción
		'test_server' => 'https://banipay.me:8443/api/', // URL DE PagosTT para Pruebas
		'user' => 'testing@banipay.me', // AppKey generado por PagosTT
		'pass' => 'testing', // AppKey generado por PagosTT
		'test_user' => 'testing@banipay.me', // AppKey generado por PagosTT
		'test_pass' => 'testing', // AppKey generado por PagosTT
		'custom_users' => ['default'=>'testing@banipay.me'], // AppKey personalizados para ser utilizados
		'custom_test_users' => ['default'=>'testing'], // AppKey personalizados para ser utilizados en modo Testing
		'custom_passwords' => ['default'=>'testing@banipay.me'], // AppKey personalizados para ser utilizados
		'custom_test_passwords' => ['default'=>'testing'], // AppKey personalizados para ser utilizados en 
		'affiliate' => '141581ae-fb1f-4cfb-b21e-040a8851c265', // Notificar la recepción del pago por correo electrónico
		'affiliate_test' => '141581ae-fb1f-4cfb-b21e-040a8851c265', // Notificar la recepción del pago por correo electrónico
		'notify_email' => true, // Notificar la recepción del pago por correo electrónico
		'enable_custom_func' => false, // Habilitar si se habilita el CustomFunc
		'enable_bridge' => false, // Habilitar si no se utilizarán los módulos de pagos de Solunes
		'finish_payment_verification' => false, // Habilitar si se desea realizar la verificación final
		'customer_all_payments' => true, // Habilitar si se desea aceptar pagos en masa
	],
	
    'cash_params' => [
		'testing' => env('ENABLE_TEST', 1), // Utilizar el ambiente de pruebas
		'redirect' => false, // Nombre enviado a Cuentas365
		'redirect_url' => null, // Nombre enviado a Cuentas365
		'app_name' => env('APP_NAME', 'PayPal'), // Nombre enviado a Cuentas365
		'enable_bridge' => false, // Habilitar si no se utilizarán los módulos de pagos de Solunes
		'finish_payment_verification' => false, // Habilitar si se desea realizar la verificación final
	],

	// PARAMETROS
	'scheduled_transactions' => false,
	'invoices' => true,
	'shipping' => false,
	'online_banks' => false,
	'custom_key' => false, // Para permitir el campo de custom appkey en tabla pagos

	// SEGURIDAD Y ENCRIPTACION
	'salt' => 'GfFJo519zBd7gzmIBhNd0vBK2Co375bS', // Llave de encriptación, reemplazar por la del proyecto
	'secret_iv' => '!IV@_$2', // Secret IV de encriptación, reemplazar por oficial de cuentas 365
	
	// PAGOSTT
	'pagostt_app_key' => 'c26d8c99-8836-4cd5-a850-230c9d3fbf3c', // AppKey generado por PagosTT
	'paypal_app_key' => 'c26d8c99-8836-4cd5-a850-230c9d3fbf3c', // AppKey generado por PagosTT

];