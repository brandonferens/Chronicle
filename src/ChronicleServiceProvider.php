<?php

namespace Kenarkose\Chronicle;


use Illuminate\Support\ServiceProvider;

class ChronicleServiceProvider extends ServiceProvider {

    const version = '1.0.1';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'chronicle',
            'Kenarkose\Chronicle\Chronicle'
        );
    }

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/resources/config.php' => config_path('chronicle.php')
        ], 'config');

        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('/migrations')
        ], 'migrations');

        require __DIR__ . '/helpers.php';
    }

}