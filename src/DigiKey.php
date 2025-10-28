<?php

namespace TONYLABS\DigiKey;

use InvalidArgumentException;
use TONYLABS\DigiKey\Product\KeywordSearchRequest;
use TONYLABS\DigiKey\Services\DigiKeyApiService;
use TONYLABS\DigiKey\Services\DigiKeyHttpClient;
use TONYLABS\DigiKey\Services\DigiKeyOAuthService;
use TONYLABS\DigiKey\Services\DigiKeyOAuthServiceRegistry;

class DigiKey extends DigiKeyApiService
{
    protected ?string $categoryFilter = null;
    protected ?string $manufacturerFilter = null;

    public function __construct(
        ?string $client_id = null,
        ?string $client_secret = null,
        array $configOverrides = []
    ) {
        $config = static::resolveConfiguration($configOverrides);

        $clientId = $client_id ?? $config['client_id'] ?? null;
        $clientSecret = $client_secret ?? $config['client_secret'] ?? null;

        if (($client_id !== null && $client_secret === null) || ($client_id === null && $client_secret !== null)) {
            throw new InvalidArgumentException('Both client_id and client_secret must be provided when supplying explicit credentials.');
        }

        if (empty($clientId) || empty($clientSecret)) {
            throw new InvalidArgumentException('DigiKey client credentials are not configured.');
        }

        $config['client_id'] = $clientId;
        $config['client_secret'] = $clientSecret;
        $oauthService = new DigiKeyOAuthService($config);
        DigiKeyOAuthServiceRegistry::setDefault($oauthService);
        $client = new DigiKeyHttpClient($oauthService, $config);
        parent::__construct($client);
    }

    public function searchKeyword(string|array|KeywordSearchRequest $search, array $options = []): object
    {
        if ($search instanceof KeywordSearchRequest) {
            $search->setOAuthService($this->getOAuthService());
            return parent::searchKeyword($search->toArrayWithValidation());
        }

        if (is_array($search)) {
            return parent::searchKeyword($search);
        }

        $keywords = $search;
        $recordCount = $options['limit'] ?? $options['recordCount'] ?? 25;
        $recordStart = $options['offset'] ?? $options['recordStartPosition'] ?? 0;
        $sort = $options['sort'] ?? 'PartNumber';
        $requestedQuantity = (string) ($options['requested_quantity'] ?? $options['requestedQuantity'] ?? '1');

        $filters = $options['filters'] ?? [];

        $request = new KeywordSearchRequest(
            keywords: $keywords,
            recordCount: $recordCount,
            recordStartPosition: $recordStart,
            filters: $filters,
            sort: $sort,
            requestedQuantity: $requestedQuantity,
            oauthService: $this->getOAuthService()
        );

        if ($this->categoryFilter !== null) {
            $request->setCategoryFilter($this->categoryFilter);
        }

        if ($this->manufacturerFilter !== null) {
            $request->setManufacturerFilter($this->manufacturerFilter);
        }

        $payload = $request->toArrayWithValidation();

        return parent::searchKeyword($payload);
    }

    public function setCategoryFilter(int|string $categoryId): self
    {
        $this->categoryFilter = (string) $categoryId;
        return $this;
    }

    public function setManufacturerFilter(int|string $manufacturerId): self
    {
        $this->manufacturerFilter = (string) $manufacturerId;
        return $this;
    }

    public function resetFilters(): self
    {
        $this->categoryFilter = null;
        $this->manufacturerFilter = null;
        return $this;
    }
}
