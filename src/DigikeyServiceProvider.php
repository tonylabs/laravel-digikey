<?php

namespace TONYLABS\Digikey;

use Illuminate\Support\ServiceProvider;
use TONYLABS\Digikey\Services\DigikeyApiService;
use TONYLABS\Digikey\Services\DigikeyHttpClient;
use TONYLABS\Digikey\Services\DigikeyOAuthService;
use TONYLABS\Digikey\Services\DigikeyOAuthServiceRegistry;

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
            $service = new DigikeyOAuthService(
                $app['config']['digikey']
            );
            DigikeyOAuthServiceRegistry::setDefault($service);

            return $service;
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
            $this->publishes([__DIR__.'/../config/digikey.php' => config_path('digikey.php'),
    ]);
        }
    }
}
