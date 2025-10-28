<?php

namespace TONYLABS\DigiKey\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use TONYLABS\DigiKey\Exceptions\DigiKeyApiException;
use TONYLABS\DigiKey\Exceptions\DigiKeyAuthenticationException;
use Psr\Http\Message\ResponseInterface;

class DigiKeyHttpClient
{
    protected array $config;
    protected Client $client;
    protected DigiKeyOAuthService $oauthService;
    public function __construct(DigiKeyOAuthService $oauthService, array $config)
    {
        $this->config = $config;
        $this->oauthService = $oauthService;
        $uri = $config['use_sandbox'] ? $config['sandbox_url'] : $config['base_url'];
        $this->client = new Client([
            'base_uri' => $uri,
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
     * @throws DigiKeyApiException
     * @throws DigiKeyAuthenticationException
     */
    public function get(string $endpoint, array $query = [], array $headers = []): object
    {
        return $this->makeRequest('GET', $endpoint, [
            'query' => $query,
            'headers' => $this->headers($headers),
        ]);
    }

    /**
     * Make a POST request
     * @throws DigiKeyApiException
     * @throws DigiKeyAuthenticationException
     */
    public function post(string $endpoint, array $data = [], array $headers = []): object
    {
        return $this->makeRequest('POST', $endpoint, [
            'json' => $data,
            'headers' => $this->headers($headers),
        ]);
    }

    /**
     * Make a PUT request
     * @throws DigiKeyApiException
     * @throws DigiKeyAuthenticationException
     */
    public function put(string $endpoint, array $data = [], array $headers = []): object
    {
        return $this->makeRequest('PUT', $endpoint, [
            'json' => $data,
            'headers' => $this->headers($headers),
        ]);
    }

    /**
     * Make a DELETE request
     * @throws DigiKeyApiException
     * @throws DigiKeyAuthenticationException
     */
    public function delete(string $endpoint, array $headers = []): object
    {
        return $this->makeRequest('DELETE', $endpoint, [
            'headers' => $this->headers($headers),
        ]);
    }

    /**
     * Make an HTTP request
     * @throws DigiKeyApiException
     */
    protected function makeRequest(string $method, string $endpoint, array $options = []): object
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);
            return $this->handleResponse($response);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($response !== null && $response->getStatusCode() === 429) {
                throw $e;
            }
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $this->handleServerException($e);
        } catch (GuzzleException $e) {
            throw new DigiKeyApiException('HTTP request failed: ' . $e->getMessage(), 0, [], $e);
        } catch (DigiKeyApiException $e) {
            throw new DigiKeyApiException('HTTP request failed: ' . $e->getMessage(), 0, [], $e);
        }
    }

    /**
     * Prepare headers with authentication
     * @throws DigiKeyAuthenticationException
     */
    protected function headers(array $headers = []): array
    {
        try {
            $accessToken = $this->oauthService->getValidAccessToken();
            $headers['Authorization'] = 'Bearer ' . $accessToken;
        } catch (\Exception $e) {
            throw new DigiKeyAuthenticationException('Failed to obtain access token: ' . $e->getMessage(), 0, $e);
        }
        if (!empty($this->config['customer_id'])) {
            $headers['X-DIGIKEY-Customer-Id'] = $this->config['customer_id'];
        }
        return $headers;
    }

    /**
     * Handle successful response
     */
    protected function handleResponse(ResponseInterface $response): object
    {
        $content = $response->getBody()->getContents();
        $data = json_decode($content, false); // false = return objects instead of arrays
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new DigiKeyApiException('Invalid JSON response: ' . json_last_error_msg());
        }
        $responseData = $data ?? new \stdClass();
        $rateLimitHeaders = $this->extractRateLimitHeaders($response);
        if (!empty($rateLimitHeaders)) {
            $responseData->RateLimit = (object) $rateLimitHeaders;
        }
        return $responseData;
    }

    /**
     * Extract rate limit headers from response
     */
    protected function extractRateLimitHeaders(ResponseInterface $response): array
    {
        $rateLimitData = [];
        if ($response->hasHeader('X-RateLimit-Limit')) {
            $rateLimitData['limit'] = (int) $response->getHeaderLine('X-RateLimit-Limit');
        }
        if ($response->hasHeader('X-RateLimit-Remaining')) {
            $rateLimitData['remaining'] = (int) $response->getHeaderLine('X-RateLimit-Remaining');
        }
        if ($response->hasHeader('X-RateLimit-Reset')) {
            $rateLimitData['reset'] = (int) $response->getHeaderLine('X-RateLimit-Reset');
        }
        if ($response->hasHeader('X-RateLimit-Window')) {
            $rateLimitData['window'] = $response->getHeaderLine('X-RateLimit-Window');
        }
        return $rateLimitData;
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
            $this->oauthService->clearCachedToken();
            throw new DigiKeyAuthenticationException($message, $statusCode, $errorData, $e);
        }
        throw new DigiKeyApiException($message, $statusCode, $errorData, $e);
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
        throw new DigiKeyApiException($message, $statusCode, $errorData, $e);
    }

    /**
     * Get the underlying HTTP client
     */
    public function getHttpClient(): Client
    {
        return $this->client;
    }

    /**
     * Get the OAuth service
     */
    public function getOAuthService(): DigiKeyOAuthService
    {
        return $this->oauthService;
    }
}
