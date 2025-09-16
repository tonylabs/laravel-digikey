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

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'timeout' => $config['http']['timeout'] ?? 30,
            'connect_timeout' => $config['http']['connect_timeout'] ?? 10,
        ]);
    }

    /**
     * Get access token using client credentials flow
     * This is the primary method for obtaining tokens
     */
    public function getAccessToken(): string
    {
        $cachedToken = Cache::get('token');
        $tokenExpires = Cache::get('token_expires');
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
            $expiresIn = $body->expires_in; // Time in seconds
            $expiryTime = time() + $expiresIn;
            Cache::put('token', $body->access_token, $expiresIn);
            Cache::put('token_expires', $expiryTime, $expiresIn);
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
        Cache::forget('token');
        Cache::forget('token_expires');
    }

    /**
     * Check if we have a valid cached token
     */
    public function hasValidToken(): bool
    {
        $cachedToken = Cache::get('token');
        $tokenExpires = Cache::get('token_expires');
        return $cachedToken && $tokenExpires && time() < $tokenExpires;
    }
}
