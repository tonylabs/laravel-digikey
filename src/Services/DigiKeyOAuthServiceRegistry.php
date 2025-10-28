<?php

namespace TONYLABS\DigiKey\Services;

class DigiKeyOAuthServiceRegistry
{
    protected static ?DigiKeyOAuthService $defaultService = null;

    public static function setDefault(?DigiKeyOAuthService $oauthService): void
    {
        static::$defaultService = $oauthService;
    }

    public static function getDefault(): ?DigiKeyOAuthService
    {
        if (static::$defaultService !== null) {
            return static::$defaultService;
        }

        if (function_exists('app')) {
            try {
                if (app()->bound(DigiKeyOAuthService::class)) {
                    return app(DigiKeyOAuthService::class);
                }
            } catch (\Throwable $exception) {
                // Ignore container resolution issues and fall through to null return.
            }
        }

        return null;
    }
}
