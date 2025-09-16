<?php

namespace TONYLABS\Digikey\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use TONYLABS\Digikey\Exceptions\DigikeyApiException;
use TONYLABS\Digikey\Exceptions\DigikeyAuthenticationException;
use Psr\Http\Message\ResponseInterface;

class DigikeyHttpClient
{
    protected Client $httpClient;
    protected DigikeyOAuthService $oauthService;
    protected array $config;

    public function __construct(DigikeyOAuthService $oauthService, array $config)
    {
        $this->oauthService = $oauthService;
        $this->config = $config;
        
        $this->httpClient = new Client([
            'base_uri' => $config['api_url'],
            'timeout' => $config['http']['timeout'] ?? 30,
            'connect_timeout' => $config['http']['connect_timeout'] ?? 10,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-DIGIKEY-Client-Id' => $config['client_id'],
                'X-DIGIKEY-Locale-Site' => $config['locale']['site'] ?? 'US',
                'X-DIGIKEY-Locale-Language' => $config['locale']['language'] ?? 'en',
                'X-DIGIKEY-Locale-Currency' => $config['locale']['currency'] ?? 'USD',
            ],
        ]);
    }

    /**
     * Make a GET request
     */
    public function get(string $endpoint, array $query = [], array $headers = []): array
    {
        return $this->makeRequest('GET', $endpoint, [
            'query' => $query,
            'headers' => $this->prepareHeaders($headers),
        ]);
    }

    /**
     * Make a POST request
     */
    public function post(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('POST', $endpoint, [
            'json' => $data,
            'headers' => $this->prepareHeaders($headers),
        ]);
    }

    /**
     * Make a PUT request
     */
    public function put(string $endpoint, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('PUT', $endpoint, [
            'json' => $data,
            'headers' => $this->prepareHeaders($headers),
        ]);
    }

    /**
     * Make a DELETE request
     */
    public function delete(string $endpoint, array $headers = []): array
    {
        return $this->makeRequest('DELETE', $endpoint, [
            'headers' => $this->prepareHeaders($headers),
        ]);
    }

    /**
     * Make an HTTP request
     */
    protected function makeRequest(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, $options);
            return $this->handleResponse($response);
        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $this->handleServerException($e);
        } catch (GuzzleException $e) {
            throw new DigikeyApiException('HTTP request failed: ' . $e->getMessage(), 0, [], $e);
        }
    }

    /**
     * Prepare headers with authentication
     */
    protected function prepareHeaders(array $additionalHeaders = []): array
    {
        $headers = $additionalHeaders;

        // Add authorization header
        try {
            $accessToken = $this->oauthService->getValidAccessToken();
            $headers['Authorization'] = 'Bearer ' . $accessToken;
        } catch (\Exception $e) {
            throw new DigikeyAuthenticationException('Failed to obtain access token: ' . $e->getMessage(), 0, $e);
        }

        // Add customer ID if configured
        if (!empty($this->config['customer_id'])) {
            $headers['X-DIGIKEY-Customer-Id'] = $this->config['customer_id'];
        }

        return $headers;
    }

    /**
     * Handle successful response
     */
    protected function handleResponse(ResponseInterface $response): array
    {
        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DigikeyApiException('Invalid JSON response: ' . json_last_error_msg());
        }

        return $data ?? [];
    }

    /**
     * Handle client exceptions (4xx errors)
     */
    protected function handleClientException(ClientException $e): void
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $content = $response->getBody()->getContents();
        
        $errorData = json_decode($content, true) ?? [];
        $message = $errorData['message'] ?? $errorData['error_description'] ?? 'Client error occurred';

        if ($statusCode === 401) {
            // Clear cached token on authentication error
            $this->oauthService->clearCachedToken();
            throw new DigikeyAuthenticationException($message, $statusCode, $errorData, $e);
        }

        throw new DigikeyApiException($message, $statusCode, $errorData, $e);
    }

    /**
     * Handle server exceptions (5xx errors)
     */
    protected function handleServerException(ServerException $e): void
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $content = $response->getBody()->getContents();
        
        $errorData = json_decode($content, true) ?? [];
        $message = $errorData['message'] ?? 'Server error occurred';

        throw new DigikeyApiException($message, $statusCode, $errorData, $e);
    }

    /**
     * Get the underlying HTTP client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * Get the OAuth service
     */
    public function getOAuthService(): DigikeyOAuthService
    {
        return $this->oauthService;
    }
}