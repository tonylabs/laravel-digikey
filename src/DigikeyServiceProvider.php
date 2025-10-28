<?php

namespace TONYLABS\DigiKey;

use Illuminate\Support\ServiceProvider;
use TONYLABS\DigiKey\Services\DigiKeyApiService;
use TONYLABS\DigiKey\Services\DigiKeyHttpClient;
use TONYLABS\DigiKey\Services\DigiKeyOAuthService;
use TONYLABS\DigiKey\Services\DigiKeyOAuthServiceRegistry;

class DigiKeyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/digikey.php', 'digikey'
        );

        $this->app->singleton(DigiKeyOAuthService::class, function ($app) {
            $service = new DigiKeyOAuthService(
                $app['config']['digikey']
            );
            DigiKeyOAuthServiceRegistry::setDefault($service);

            return $service;
        });

        $this->app->singleton(DigiKeyHttpClient::class, function ($app) {
            return new DigiKeyHttpClient(
                $app->make(DigiKeyOAuthService::class),
                $app['config']['digikey']
            );
        });

        $this->app->singleton(DigiKeyApiService::class, function ($app) {
            return new DigiKeyApiService(
                $app->make(DigiKeyHttpClient::class)
            );
        });

        $this->app->alias(DigiKeyApiService::class, 'digikey.api');

        $this->app->singleton(DigiKey::class, function () {
            return new DigiKey();
        });

        $this->app->alias(DigiKey::class, 'digikey');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/digikey.php' => config_path('digikey.php'),
            ]);
        }
    }
}
