<?php

namespace TONYLABS\DigiKey\Product;

use TONYLABS\DigiKey\Services\DigiKeyOAuthService;
use TONYLABS\DigiKey\Services\DigiKeyOAuthServiceRegistry;
use TONYLABS\DigiKey\Exceptions\DigiKeyAuthenticationException;

class KeywordSearchRequest
{
    public string $keywords;
    public int $recordCount;
    public int $recordStartPosition;
    public array $filters;
    public string $requestedQuantity;
    protected ?SortField $sortField;
    protected ?SortOrder $sortOrder;
    protected ?DigiKeyOAuthService $oauthService = null;

    public function __construct(
        string $keywords,
        int $recordCount = 25,
        int $recordStartPosition = 0,
        array $filters = [],
        SortField|string|null $sortField = null,
        string $requestedQuantity = '1',
        ?DigiKeyOAuthService $oauthService = null,
        SortOrder|string|null $sortOrder = null
    ) {
        $this->keywords = $keywords;
        $this->recordCount = $recordCount;
        $this->recordStartPosition = $recordStartPosition;
        $this->filters = $filters;
        $this->requestedQuantity = $requestedQuantity;
        $this->oauthService = $oauthService;
        $this->sortField = $this->resolveSortField($sortField);
        $this->sortOrder = $this->resolveSortOrder($sortOrder);
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
            $categoryFilters[] = ['Id' => (string) $categoryId];
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
    public function setOAuthService(DigiKeyOAuthService $oauthService): self
    {
        $this->oauthService = $oauthService;
        return $this;
    }

    /**
     * Validate that we have a valid access token before making the request
     * 
     * @throws DigiKeyAuthenticationException
     */
    public function validateToken(): bool
    {
        if ($this->oauthService === null) {
            $this->oauthService = DigiKeyOAuthServiceRegistry::getDefault();
        }

        if ($this->oauthService === null && function_exists('app')) {
            try {
                // Fall back to resolving via the container when available
                $this->oauthService = app(DigiKeyOAuthService::class);
            } catch (\Throwable $exception) {
                // Leave oauthService null so we surface a clear authentication error below.
            }
        }

        if ($this->oauthService === null) {
            throw new DigiKeyAuthenticationException('Unable to resolve DigiKeyOAuthService instance for token validation.');
        }

        if (!$this->oauthService->hasValidToken()) {
            // Try to get a new token
            try {
                $this->oauthService->getAccessToken();
                return true;
            } catch (\Exception $e) {
                throw new DigiKeyAuthenticationException(
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

        // Add SortOptions if configured
        if ($this->sortField !== null || $this->sortOrder !== null) {
            $sortOptions = [];

            if ($this->sortField !== null) {
                $sortOptions['Field'] = $this->sortField->value;
            }

            if ($this->sortOrder !== null) {
                $sortOptions['SortOrder'] = $this->sortOrder->value;
            }

            if (!empty($sortOptions)) {
                $request['SortOptions'] = $sortOptions;
            }
        }

        return $request;
    }

    public static function fromArray(array $data, ?DigiKeyOAuthService $oauthService = null): self
    {
        $filters = [];
        if (isset($data['FilterOptionsRequest'])) {
            $filters['FilterOptionsRequest'] = $data['FilterOptionsRequest'];
        }

        $sortField = null;
        if (isset($data['SortOptions']['Field'])) {
            $sortField = $data['SortOptions']['Field'];
        } elseif (isset($data['SortOptions']['SortBy'])) {
            $sortField = $data['SortOptions']['SortBy'];
        }

        $sortOrder = $data['SortOptions']['SortOrder'] ?? null;

        return new self(
            $data['Keywords'] ?? '',
            $data['Limit'] ?? $data['RecordCount'] ?? 25,
            $data['Offset'] ?? $data['RecordStartPosition'] ?? 0,
            $filters,
            $sortField,
            $data['RequestedQuantity'] ?? '1',
            $oauthService,
            $sortOrder
        );
    }

    protected function resolveSortField(SortField|string|null $sortField): ?SortField
    {
        if ($sortField === null) {
            return null;
        }

        if ($sortField instanceof SortField) {
            return $sortField;
        }

        return SortField::fromValue($sortField);
    }

    protected function resolveSortOrder(SortOrder|string|null $sortOrder): ?SortOrder
    {
        if ($sortOrder === null) {
            return null;
        }

        if ($sortOrder instanceof SortOrder) {
            return $sortOrder;
        }

        return SortOrder::fromValue($sortOrder);
    }
}
