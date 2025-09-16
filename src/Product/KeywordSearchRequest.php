<?php

namespace TONYLABS\Digikey\Product;

use TONYLABS\Digikey\Services\DigikeyOAuthService;
use TONYLABS\Digikey\Exceptions\DigikeyAuthenticationException;

class KeywordSearchRequest
{
    public string $keywords;
    public int $recordCount;
    public int $recordStartPosition;
    public array $filters;
    public string $sort;
    public string $requestedQuantity;
    protected ?DigikeyOAuthService $oauthService = null;

    public function __construct(
        string $keywords,
        int $recordCount = 25,
        int $recordStartPosition = 0,
        array $filters = [],
        string $sort = 'PartNumber',
        string $requestedQuantity = '1',
        ?DigikeyOAuthService $oauthService = null
    ) {
        $this->keywords = $keywords;
        $this->recordCount = $recordCount;
        $this->recordStartPosition = $recordStartPosition;
        $this->filters = $filters;
        $this->sort = $sort;
        $this->requestedQuantity = $requestedQuantity;
        $this->oauthService = $oauthService;
    }

    /**
     * Set category filter by category ID
     */
    public function setCategoryFilter(string $categoryId): self
    {
        if (!isset($this->filters['FilterOptionsRequest'])) {
            $this->filters['FilterOptionsRequest'] = [];
        }
        
        $this->filters['FilterOptionsRequest']['CategoryFilter'] = [
            ['Id' => $categoryId]
        ];
        
        return $this;
    }

    /**
     * Add multiple category filters
     */
    public function setCategoryFilters(array $categoryIds): self
    {
        if (!isset($this->filters['FilterOptionsRequest'])) {
            $this->filters['FilterOptionsRequest'] = [];
        }
        
        $categoryFilters = [];
        foreach ($categoryIds as $categoryId) {
            $categoryFilters[] = ['Id' => (string)$categoryId];
        }
        
        $this->filters['FilterOptionsRequest']['CategoryFilter'] = $categoryFilters;
        
        return $this;
    }

    /**
     * Set manufacturer filter by manufacturer ID
     */
    public function setManufacturerFilter(string $manufacturerId): self
    {
        if (!isset($this->filters['FilterOptionsRequest'])) {
            $this->filters['FilterOptionsRequest'] = [];
        }
        
        $this->filters['FilterOptionsRequest']['ManufacturerFilter'] = [
            ['Id' => $manufacturerId]
        ];
        
        return $this;
    }

    /**
     * Set search options (e.g., InStock, RohsCompliant, etc.)
     */
    public function setSearchOptions(array $options): self
    {
        if (!isset($this->filters['FilterOptionsRequest'])) {
            $this->filters['FilterOptionsRequest'] = [];
        }
        
        $this->filters['FilterOptionsRequest']['SearchOptions'] = $options;
        
        return $this;
    }

    /**
     * Set minimum quantity available filter
     */
    public function setMinimumQuantityAvailable(int $quantity): self
    {
        if (!isset($this->filters['FilterOptionsRequest'])) {
            $this->filters['FilterOptionsRequest'] = [];
        }
        
        $this->filters['FilterOptionsRequest']['MinimumQuantityAvailable'] = $quantity;
        
        return $this;
    }

    /**
     * Set the OAuth service for token validation
     */
    public function setOAuthService(DigikeyOAuthService $oauthService): self
    {
        $this->oauthService = $oauthService;
        return $this;
    }

    /**
     * Validate that we have a valid access token before making the request
     * 
     * @throws DigikeyAuthenticationException
     */
    public function validateToken(): bool
    {
        if ($this->oauthService === null) {
            // If no OAuth service is injected, try to resolve it from the container
            $this->oauthService = app(DigikeyOAuthService::class);
        }

        if (!$this->oauthService->hasValidToken()) {
            // Try to get a new token
            try {
                $this->oauthService->getAccessToken();
                return true;
            } catch (\Exception $e) {
                throw new DigikeyAuthenticationException(
                    'Unable to obtain valid access token: ' . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        return true;
    }

    /**
     * Ensure we have a valid token and return the request data
     * This method should be called before making API requests
     */
    public function toArrayWithValidation(): array
    {
        $this->validateToken();
        return $this->toArray();
    }

    public function toArray(): array
    {
        $request = [
            'Keywords' => $this->keywords,
            'Limit' => $this->recordCount,
            'Offset' => $this->recordStartPosition,
        ];

        // Add FilterOptionsRequest if filters are set
        if (!empty($this->filters['FilterOptionsRequest'])) {
            $request['FilterOptionsRequest'] = $this->filters['FilterOptionsRequest'];
        }

        // Add SortOptions if sort is specified
        if ($this->sort !== 'PartNumber') {
            $request['SortOptions'] = [
                'SortBy' => $this->sort
            ];
        }

        return $request;
    }

    public static function fromArray(array $data, ?DigikeyOAuthService $oauthService = null): self
    {
        $filters = [];
        if (isset($data['FilterOptionsRequest'])) {
            $filters['FilterOptionsRequest'] = $data['FilterOptionsRequest'];
        }

        $sort = 'PartNumber';
        if (isset($data['SortOptions']['SortBy'])) {
            $sort = $data['SortOptions']['SortBy'];
        }

        return new self(
            $data['Keywords'] ?? '',
            $data['Limit'] ?? $data['RecordCount'] ?? 25,
            $data['Offset'] ?? $data['RecordStartPosition'] ?? 0,
            $filters,
            $sort,
            $data['RequestedQuantity'] ?? '1',
            $oauthService
        );
    }
}