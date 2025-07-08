<?php

namespace RandomStrInc\LaravelAfricanCurrencies;

use Illuminate\Support\ServiceProvider;

class LaravelAfricanCurrenciesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/african-currencies.php', 'african-currencies');

        $this->app->singleton('african-currency', function ($app) {
            return new AfricanCurrencyConverter();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/african-currencies.php' => config_path('african-currencies.php'),
        ], 'african-currencies-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\FetchBozRatesCommand::class,
                Console\Commands\InstallSetupCommand::class,
            ]);
        }
    }
}
