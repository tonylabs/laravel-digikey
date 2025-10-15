<?php

namespace TONYLABS\Digikey\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use TONYLABS\Digikey\Exceptions\DigikeyAuthenticationException;

class DigikeyOAuthService
{
    protected Client $client;
    protected array $config;
    protected string $tokenCacheKey;
    protected string $tokenExpiryCacheKey;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'timeout' => $config['http']['timeout'] ?? 30,
            'connect_timeout' => $config['http']['connect_timeout'] ?? 10,
        ]);
        $this->tokenCacheKey = $this->buildCacheKey($config['cache']['token_key'] ?? 'digikey_access_token');
        $this->tokenExpiryCacheKey = $this->tokenCacheKey . '_expires';
    }

    /**
     * Get access token using client credentials flow
     * This is the primary method for obtaining tokens
     */
    public function getAccessToken(): string
    {
        $cachedToken = Cache::get($this->tokenCacheKey);
        $tokenExpires = Cache::get($this->tokenExpiryCacheKey);
        if ($cachedToken && $tokenExpires && time() < $tokenExpires) {
            return $cachedToken;
        }
        try {
            $response = $this->client->post($this->config['oauth']['token_url'], [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                ]
            ]);
            $body = json_decode($response->getBody()->getContents());
            if (!isset($body->access_token)) {
                throw new DigikeyAuthenticationException('Invalid token response: access_token not found');
            }
            $expiresIn = isset($body->expires_in) ? (int) $body->expires_in : 0;
            if ($expiresIn <= 0) {
                $expiresIn = $this->config['cache']['token_ttl'] ?? 3600;
            }
            $expiryTime = time() + $expiresIn;
            Cache::put($this->tokenCacheKey, $body->access_token, $expiresIn);
            Cache::put($this->tokenExpiryCacheKey, $expiryTime, $expiresIn);
            return $body->access_token;

        } catch (GuzzleException $e) {
            throw new DigikeyAuthenticationException('Failed to obtain access token: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get cached access token or obtain a new one
     * Alias for getAccessToken() for backward compatibility
     */
    public function getValidAccessToken(): string
    {
        return $this->getAccessToken();
    }

    /**
     * Clear cached token
     */
    public function clearCachedToken(): void
    {
        Cache::forget($this->tokenCacheKey);
        Cache::forget($this->tokenExpiryCacheKey);
    }

    /**
     * Check if we have a valid cached token
     */
    public function hasValidToken(): bool
    {
        $cachedToken = Cache::get($this->tokenCacheKey);
        $tokenExpires = Cache::get($this->tokenExpiryCacheKey);
        return $cachedToken && $tokenExpires && time() < $tokenExpires;
    }

    /**
     * Build a cache key that is namespaced per client.
     */
    protected function buildCacheKey(string $baseKey): string
    {
        $clientId = $this->config['client_id'] ?? null;
        if (!$clientId) {
            return $baseKey;
        }

        return $baseKey . ':' . sha1($clientId);
    }
}
