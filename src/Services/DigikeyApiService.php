<?php

namespace TONYLABS\Digikey\Services;

class DigikeyApiService
{
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
        return $this->client->post('/search/keyword', $searchRequest);
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

        return $this->client->get("/search/{$productNumber}/productdetails", $query);
    }

    /**
     * Get list of manufacturers
     * GET /search/manufacturers
     */
    public function getManufacturers(): object
    {
        return $this->client->get('/search/manufacturers');
    }

    /**
     * Get list of categories
     * GET /search/categories
     */
    public function getCategories(): object
    {
        return $this->client->get('/search/categories');
    }

    /**
     * Get category details by category ID
     * GET /search/categories/{categoryId}
     */
    public function getCategoryDetails(int $categoryId): object
    {
        return $this->client->get("/search/categories/{$categoryId}");
    }

    /**
     * Get DigiReel pricing for a product
     * GET /search/{productNumber}/digireelpricing
     */
    public function getDigiReelPricing(string $productNumber, int $requestedQuantity): object
    {
        return $this->client->get("/search/{$productNumber}/digireelpricing", [
            'requestedquantity' => $requestedQuantity,
        ]);
    }

    /**
     * Get recommended products for a product
     * GET /search/{productNumber}/recommendedproducts
     */
    public function getRecommendedProducts(string $productNumber): object
    {
        return $this->client->get("/search/{$productNumber}/recommendedproducts");
    }

    /**
     * Get product substitutions
     * GET /search/{productNumber}/substitutions
     */
    public function getProductSubstitutions(string $productNumber): object
    {
        return $this->client->get("/search/{$productNumber}/substitutions");
    }

    /**
     * Get product associations
     * GET /search/{productNumber}/associations
     */
    public function getProductAssociations(string $productNumber): object
    {
        return $this->client->get("/search/{$productNumber}/associations");
    }

    /**
     * Get package type by quantity (deprecated)
     * GET /search/packagetypebyquantity/{productNumber}
     */
    public function getPackageTypeByQuantity(string $productNumber, int $requestedQuantity): object
    {
        return $this->client->get("/search/packagetypebyquantity/{$productNumber}", [
            'requestedquantity' => $requestedQuantity,
        ]);
    }

    /**
     * Get product media
     * GET /search/{productNumber}/media
     */
    public function getProductMedia(string $productNumber): array
    {
        return $this->client->get("/search/{$productNumber}/media");
    }

    /**
     * Get product pricing
     * GET /search/{productNumber}/pricing
     */
    public function getProductPricing(string $productNumber, int $requestedQuantity): array
    {
        return $this->client->get("/search/{$productNumber}/pricing", [
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
}
