<?php

namespace TONYLABS\Digikey\Product;

class KeywordSearchRequest
{
    public string $keywords;
    public int $recordCount;
    public int $recordStartPosition;
    public array $filters;
    public string $sort;
    public string $requestedQuantity;

    public function __construct(
        string $keywords,
        int $recordCount = 25,
        int $recordStartPosition = 0,
        array $filters = [],
        string $sort = 'PartNumber',
        string $requestedQuantity = '1'
    ) {
        $this->keywords = $keywords;
        $this->recordCount = $recordCount;
        $this->recordStartPosition = $recordStartPosition;
        $this->filters = $filters;
        $this->sort = $sort;
        $this->requestedQuantity = $requestedQuantity;
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

    public static function fromArray(array $data): self
    {
        return new self(
            $data['Keywords'] ?? '',
            $data['RecordCount'] ?? 25,
            $data['RecordStartPosition'] ?? 0,
            $data['Filters'] ?? [],
            $data['Sort'] ?? 'PartNumber',
            $data['RequestedQuantity'] ?? '1'
        );
    }
}