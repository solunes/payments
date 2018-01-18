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
        $this->app->register('Anouar\Paypalpayment\PaypalpaymentServiceProvider');

        /* Registrar Alias */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Paypalpayment', 'Anouar\Paypalpayment\Facades\PaypalPayment');

        $loader->alias('Payments', '\Solunes\Payments\App\Helpers\Payments');

        /* Comandos de Consola */
        $this->commands([
            \Solunes\Payments\App\Console\TestEncryption::class,
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/config/payments.php', 'payments'
        );
    }
    
}
