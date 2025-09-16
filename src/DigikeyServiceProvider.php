<?php

namespace TONYLABS\Digikey;

use Illuminate\Support\ServiceProvider;
use TONYLABS\Digikey\Services\DigikeyApiService;
use TONYLABS\Digikey\Services\DigikeyHttpClient;
use TONYLABS\Digikey\Services\DigikeyOAuthService;

class DigikeyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/digikey.php', 'digikey'
        );

        $this->app->singleton(DigikeyOAuthService::class, function ($app) {
            return new DigikeyOAuthService(
                $app['config']['digikey']
            );
        });

        $this->app->singleton(DigikeyHttpClient::class, function ($app) {
            return new DigikeyHttpClient(
                $app->make(DigikeyOAuthService::class),
                $app['config']['digikey']
            );
        });

        $this->app->singleton(DigikeyApiService::class, function ($app) {
            return new DigikeyApiService(
                $app->make(DigikeyHttpClient::class)
            );
        });

        $this->app->alias(DigikeyApiService::class, 'digikey');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/digikey.php' => config_path('digikey.php'),
            ], 'digikey-config');
        }
    }
}