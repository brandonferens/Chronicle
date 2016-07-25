<?php

namespace Kenarkose\Chronicle;


use Illuminate\Support\ServiceProvider;

class ChronicleServiceProvider extends ServiceProvider {

    const version = '1.2.1';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['chronicle'];
    }

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
        if ( ! $this->app->environment('production'))
        {
            $this->publishes([
                __DIR__ . '/resources/config.php' => config_path('chronicle.php')
            ], 'config');

            $this->publishes([
                __DIR__ . '/database/migrations/' => database_path('/migrations')
            ], 'migrations');
        }
    }

}