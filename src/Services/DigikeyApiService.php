<?php

namespace TONYLABS\Digikey\Services;

class DigikeyApiService {
    protected DigikeyHttpClient $client;
    public function __construct(DigikeyHttpClient $client)
    {
        $this->client = $client;
    }
    /**
     * Search for products using keywords
     * POST /search/keyword
     */
    public function searchKeyword(array $searchRequest): object
    {
        return $this->client->post('/products/v4/search/keyword', $searchRequest);
    }
    /**
     * Get product details by product number
     * GET /search/{productNumber}/productdetails
     */
    public function getProductDetails(string $productNumber, array $includes = [], array $excludes = []): object
    {
        $query = [];
        if (!empty($includes)) {
            $query['includes'] = implode(',', $includes);
        }
        if (!empty($excludes)) {
            $query['excludes'] = implode(',', $excludes);
        }
        return $this->client->get("/products/v4/search/{$productNumber}/productdetails", $query);
    }

    /**
     * Get list of manufacturers
     * GET /search/manufacturers
     */
    public function getManufacturers(): object
    {
        return $this->client->get('/products/v4/search/manufacturers');
    }

    /**
     * Get list of categories
     * GET /search/categories
     */
    public function getCategories(): object
    {
        return $this->client->get('/products/v4/search/categories');
    }

    /**
     * Get category details by category ID
     * GET /search/categories/{categoryId}
     */
    public function getCategoryDetails(int $categoryId): object
    {
        return $this->client->get("/products/v4/search/categories/{$categoryId}");
    }

    /**
     * Get DigiReel pricing for a product
     * GET /search/{productNumber}/digireelpricing
     */
    public function getDigiReelPricing(string $productNumber, int $requestedQuantity): object
    {
        return $this->client->get("/products/v4/search/{$productNumber}/digireelpricing", [
            'requestedquantity' => $requestedQuantity,
        ]);
    }

    /**
     * Get recommended products for a product
     * GET /search/{productNumber}/recommendedproducts
     */
    public function getRecommendedProducts(string $productNumber): object
    {
        return $this->client->get("/products/v4/search/{$productNumber}/recommendedproducts");
    }

    /**
     * Get product substitutions
     * GET /search/{productNumber}/substitutions
     */
    public function getProductSubstitutions(string $productNumber): object
    {
        return $this->client->get("/products/v4/search/{$productNumber}/substitutions");
    }

    /**
     * Get product associations
     * GET /search/{productNumber}/associations
     */
    public function getProductAssociations(string $productNumber): object
    {
        return $this->client->get("/products/v4/search/{$productNumber}/associations");
    }

    /**
     * Get package type by quantity (deprecated)
     * GET /search/packagetypebyquantity/{productNumber}
     */
    public function getPackageTypeByQuantity(string $productNumber, int $requestedQuantity): object
    {
        return $this->client->get("/products/v4/search/packagetypebyquantity/{$productNumber}", [
            'requestedquantity' => $requestedQuantity,
        ]);
    }

    /**
     * Get product media
     * GET /search/{productNumber}/media
     */
    public function getProductMedia(string $productNumber): array
    {
        return $this->client->get("/products/v4/search/{$productNumber}/media");
    }

    /**
     * Get product pricing
     * GET /search/{productNumber}/pricing
     */
    public function getProductPricing(string $productNumber, int $requestedQuantity): array
    {
        return $this->client->get("/products/v4/search/{$productNumber}/pricing", [
            'requestedquantity' => $requestedQuantity,
        ]);
    }

    /**
     * Get the underlying HTTP client
     */
    public function getHttpClient(): DigikeyHttpClient
    {
        return $this->client;
    }

    /**
     * Get OAuth service through HTTP client
     */
    public function getOAuthService(): DigikeyOAuthService
    {
        return $this->client->getOAuthService();
    }

    /**
     * Create a Digikey API service instance with explicit credentials.
     */
    public static function createWithCredentials(string $client_id, string $client_secret, array $configOverrides = []): self
    {
        $config = static::resolveConfiguration($configOverrides);
        $config['client_id'] = $client_id;
        $config['client_secret'] = $client_secret;
        $objOAuthService = new DigikeyOAuthService($config);
        $objClient = new DigikeyHttpClient($objOAuthService, $config);
        return new self($objClient);
    }

    /**
     * Merge the default package configuration with any runtime overrides.
     */
    protected static function resolveConfiguration(array $configOverrides = []): array
    {
        $defaults = [
            'client_id' => null,
            'client_secret' => null,
            'base_url' => 'https://api.digikey.com',
            'sandbox_url' => 'https://sandbox-api.digikey.com',
            'use_sandbox' => false,
            'oauth' => [
                'authorization_url' => 'https://api.digikey.com/v1/oauth2/authorize',
                'token_url' => 'https://api.digikey.com/v1/oauth2/token',
                'redirect_uri' => null,
                'scope' => '',
            ],
            'locale' => [
                'language' => 'en',
                'currency' => 'USD',
                'site' => 'US',
            ],
            'customer_id' => null,
            'cache' => [
                'token_key' => 'digikey_access_token',
                'token_ttl' => 3600,
            ],
            'http' => [
                'timeout' => 30,
                'connect_timeout' => 10,
                'retry_attempts' => 3,
            ],
        ];

        if (function_exists('config')) {
            $runtimeConfig = config('digikey', []);
            if (is_array($runtimeConfig) && !empty($runtimeConfig)) {
                $defaults = array_replace_recursive($defaults, $runtimeConfig);
            }
        }

        return array_replace_recursive($defaults, $configOverrides);
    }
}
