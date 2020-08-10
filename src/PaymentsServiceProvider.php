<?php

namespace Solunes\Payments;

use Illuminate\Support\ServiceProvider;

class PaymentsServiceProvider extends ServiceProvider {

    protected $defer = false;

    public function boot() {
        /* Publicar Elementos */
        $this->publishes([
            __DIR__ . '/config' => config_path()
        ], 'config');
        $this->publishes([
            __DIR__.'/assets' => public_path('assets/payments'),
        ], 'assets');

        /* Cargar Traducciones */
        $this->loadTranslationsFrom(__DIR__.'/lang', 'payments');

        /* Cargar Vistas */
        $this->loadViewsFrom(__DIR__ . '/views', 'payments');
    }


    public function register() {
        /* Registrar ServiceProvider Internos */
        $this->app->register('Ignited\LaravelOmnipay\LaravelOmnipayServiceProvider');

        /* Registrar Alias */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Omnipay', 'Ignited\LaravelOmnipay\Facades\OmnipayFacade');

        $loader->alias('Pagostt', '\Solunes\Payments\App\Helpers\Pagostt');
        $loader->alias('Pagatodo', '\Solunes\Payments\App\Helpers\Pagatodo');
        $loader->alias('Payme', '\Solunes\Payments\App\Helpers\Payme');
        $loader->alias('OmnipayGateway', '\Solunes\Payments\App\Helpers\OmnipayGateway');
        $loader->alias('Payments', '\Solunes\Payments\App\Helpers\Payments');
        $loader->alias('TestPayment', '\Solunes\Payments\App\Helpers\TestPayment');
        $loader->alias('CashPayment', '\Solunes\Payments\App\Helpers\CashPayment');
        $loader->alias('BankDeposit', '\Solunes\Payments\App\Helpers\BankDeposit');

        /* Comandos de Consola */
        $this->commands([
            \Solunes\Payments\App\Console\TestEncryption::class,
            \Solunes\Payments\App\Console\TestPagostt::class,
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/config/payments.php', 'payments'
        );
    }
    
}
