<?php

namespace TONYLABS\Digikey\Services;

class DigikeyOAuthServiceRegistry
{
    protected static ?DigikeyOAuthService $defaultService = null;

    public static function setDefault(?DigikeyOAuthService $oauthService): void
    {
        static::$defaultService = $oauthService;
    }

    public static function getDefault(): ?DigikeyOAuthService
    {
        if (static::$defaultService !== null) {
            return static::$defaultService;
        }

        if (function_exists('app')) {
            try {
                if (app()->bound(DigikeyOAuthService::class)) {
                    return app(DigikeyOAuthService::class);
                }
            } catch (\Throwable $exception) {
                // Ignore container resolution issues and fall through to null return.
            }
        }

        return null;
    }
}
