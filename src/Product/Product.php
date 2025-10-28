<?php

namespace TONYLABS\DigiKey\Product;

class Product
{
    public ?string $digiKeyPartNumber;
    public ?string $manufacturerPartNumber;
    public ?string $manufacturer;
    public ?string $productDescription;
    public ?string $detailedDescription;
    public ?int $quantityAvailable;
    public ?float $unitPrice;
    public ?int $minimumOrderQuantity;
    public ?string $packaging;
    public ?string $series;
    public ?string $productStatus;
    public ?string $productUrl;
    public ?string $datasheetUrl;
    public ?string $photoUrl;
    public ?array $parameters;
    public ?array $alternatePackaging;

    public function __construct(array $data = [])
    {
        $this->digiKeyPartNumber = $data['DigiKeyPartNumber'] ?? null;
        $this->manufacturerPartNumber = $data['ManufacturerPartNumber'] ?? null;
        $this->manufacturer = $data['Manufacturer']['Value'] ?? $data['Manufacturer'] ?? null;
        $this->productDescription = $data['ProductDescription'] ?? null;
        $this->detailedDescription = $data['DetailedDescription'] ?? null;
        $this->quantityAvailable = $data['QuantityAvailable'] ?? null;
        $this->unitPrice = $data['UnitPrice'] ?? null;
        $this->minimumOrderQuantity = $data['MinimumOrderQuantity'] ?? null;
        $this->packaging = $data['Packaging']['Value'] ?? $data['Packaging'] ?? null;
        $this->series = $data['Series']['Value'] ?? $data['Series'] ?? null;
        $this->productStatus = $data['ProductStatus']['Value'] ?? $data['ProductStatus'] ?? null;
        $this->productUrl = $data['ProductUrl'] ?? null;
        $this->datasheetUrl = $data['DatasheetUrl'] ?? null;
        $this->photoUrl = $data['PhotoUrl'] ?? null;
        $this->parameters = $data['Parameters'] ?? null;
        $this->alternatePackaging = $data['AlternatePackaging'] ?? null;
    }

    public function toArray(): array
    {
        return array_filter([
            'DigiKeyPartNumber' => $this->digiKeyPartNumber,
            'ManufacturerPartNumber' => $this->manufacturerPartNumber,
            'Manufacturer' => $this->manufacturer,
            'ProductDescription' => $this->productDescription,
            'DetailedDescription' => $this->detailedDescription,
            'QuantityAvailable' => $this->quantityAvailable,
            'UnitPrice' => $this->unitPrice,
            'MinimumOrderQuantity' => $this->minimumOrderQuantity,
            'Packaging' => $this->packaging,
            'Series' => $this->series,
            'ProductStatus' => $this->productStatus,
            'ProductUrl' => $this->productUrl,
            'DatasheetUrl' => $this->datasheetUrl,
            'PhotoUrl' => $this->photoUrl,
            'Parameters' => $this->parameters,
            'AlternatePackaging' => $this->alternatePackaging,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}