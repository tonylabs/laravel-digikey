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
        return [
            'Keywords' => $this->keywords,
            'RecordCount' => $this->recordCount,
            'RecordStartPosition' => $this->recordStartPosition,
            'Filters' => $this->filters,
            'Sort' => $this->sort,
            'RequestedQuantity' => $this->requestedQuantity,
        ];
    }

    public static function fromArray(array $data, ?DigikeyOAuthService $oauthService = null): self
    {
        return new self(
            $data['Keywords'] ?? '',
            $data['RecordCount'] ?? 25,
            $data['RecordStartPosition'] ?? 0,
            $data['Filters'] ?? [],
            $data['Sort'] ?? 'PartNumber',
            $data['RequestedQuantity'] ?? '1',
            $oauthService
        );
    }
}