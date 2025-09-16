<?php

namespace TONYLABS\Digikey\Product;

class SearchResponse
{
    public int $productsCount;
    public int $exactManufacturerProductsCount;
    public int $exactDigiKeyProductsCount;
    public int $exactManufacturerProducts;
    public int $exactDigiKeyProducts;
    public array $products;
    public ?array $filterOptions;

    public function __construct(array $data = [])
    {
        $this->productsCount = $data['ProductsCount'] ?? 0;
        $this->exactManufacturerProductsCount = $data['ExactManufacturerProductsCount'] ?? 0;
        $this->exactDigiKeyProductsCount = $data['ExactDigiKeyProductsCount'] ?? 0;
        $this->exactManufacturerProducts = $data['ExactManufacturerProducts'] ?? 0;
        $this->exactDigiKeyProducts = $data['ExactDigiKeyProducts'] ?? 0;
        
        $this->products = array_map(
            fn($productData) => new Product($productData),
            $data['Products'] ?? []
        );
        
        $this->filterOptions = $data['FilterOptions'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'ProductsCount' => $this->productsCount,
            'ExactManufacturerProductsCount' => $this->exactManufacturerProductsCount,
            'ExactDigiKeyProductsCount' => $this->exactDigiKeyProductsCount,
            'ExactManufacturerProducts' => $this->exactManufacturerProducts,
            'ExactDigiKeyProducts' => $this->exactDigiKeyProducts,
            'Products' => array_map(fn(Product $product) => $product->toArray(), $this->products),
            'FilterOptions' => $this->filterOptions,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getProductsCount(): int
    {
        return $this->productsCount;
    }

    public function hasProducts(): bool
    {
        return $this->productsCount > 0;
    }
}