<?php

namespace TONYLABS\Digikey\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use TONYLABS\Digikey\Exceptions\DigikeyAuthenticationException;

class DigikeyOAuthService
{
    protected Client $httpClient;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->httpClient = new Client([
            'timeout' => $config['http']['timeout'] ?? 30,
            'connect_timeout' => $config['http']['connect_timeout'] ?? 10,
        ]);
    }

    /**
     * Get the authorization URL for OAuth2 flow
     */
    public function getAuthorizationUrl(string $state = null): string
    {
        $params = [
            'response_type' => 'code',
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['oauth']['redirect_uri'],
        ];

        if ($this->config['oauth']['scope']) {
            $params['scope'] = $this->config['oauth']['scope'];
        }

        if ($state) {
            $params['state'] = $state;
        }

        return $this->config['oauth']['authorization_url'] . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $code): array
    {
        try {
            $response = $this->httpClient->post($this->config['oauth']['token_url'], [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->config['oauth']['redirect_uri'],
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['access_token'])) {
                $this->cacheToken($data);
                return $data;
            }

            throw new DigikeyAuthenticationException('Invalid token response: ' . json_encode($data));

        } catch (GuzzleException $e) {
            throw new DigikeyAuthenticationException('Failed to obtain access token: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get access token using client credentials flow (two-legged OAuth)
     */
    public function getClientCredentialsToken(): array
    {
        try {
            $response = $this->httpClient->post($this->config['oauth']['token_url'], [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['access_token'])) {
                $this->cacheToken($data);
                return $data;
            }

            throw new DigikeyAuthenticationException('Invalid token response: ' . json_encode($data));

        } catch (GuzzleException $e) {
            throw new DigikeyAuthenticationException('Failed to obtain client credentials token: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshToken(string $refreshToken): array
    {
        try {
            $response = $this->httpClient->post($this->config['oauth']['token_url'], [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret'],
                ],
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['access_token'])) {
                $this->cacheToken($data);
                return $data;
            }

            throw new DigikeyAuthenticationException('Invalid refresh token response: ' . json_encode($data));

        } catch (GuzzleException $e) {
            throw new DigikeyAuthenticationException('Failed to refresh token: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get cached access token or obtain a new one
     */
    public function getValidAccessToken(): string
    {
        $cacheKey = $this->config['cache']['token_key'];
        $cachedToken = Cache::get($cacheKey);

        if ($cachedToken && isset($cachedToken['access_token'])) {
            return $cachedToken['access_token'];
        }

        // Try to get a new token using client credentials
        $tokenData = $this->getClientCredentialsToken();
        return $tokenData['access_token'];
    }

    /**
     * Cache the token data
     */
    protected function cacheToken(array $tokenData): void
    {
        $cacheKey = $this->config['cache']['token_key'];
        $ttl = isset($tokenData['expires_in']) 
            ? $tokenData['expires_in'] - 60 // Subtract 60 seconds for safety
            : $this->config['cache']['token_ttl'];

        Cache::put($cacheKey, $tokenData, $ttl);
    }

    /**
     * Clear cached token
     */
    public function clearCachedToken(): void
    {
        Cache::forget($this->config['cache']['token_key']);
    }

    /**
     * Check if we have a valid cached token
     */
    public function hasValidToken(): bool
    {
        $cacheKey = $this->config['cache']['token_key'];
        $cachedToken = Cache::get($cacheKey);

        return $cachedToken && isset($cachedToken['access_token']);
    }
}