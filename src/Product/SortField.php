<?php

namespace TONYLABS\DigiKey\Product;

use InvalidArgumentException;

enum SortField: string
{
    case None = 'None';
    case Packaging = 'Packaging';
    case ProductStatus = 'ProductStatus';
    case DigiKeyProductNumber = 'DigiKeyProductNumber';
    case ManufacturerProductNumber = 'ManufacturerProductNumber';
    case Manufacturer = 'Manufacturer';
    case MinimumQuantity = 'MinimumQuantity';
    case QuantityAvailable = 'QuantityAvailable';
    case Price = 'Price';
    case Supplier = 'Supplier';
    case PriceManufacturerStandardPackage = 'PriceManufacturerStandardPackage';

    /**
     * Resolve a sort field from a case-insensitive string, supporting legacy aliases.
     */
    public static function fromValue(string $value): self
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            throw new InvalidArgumentException('Sort field cannot be empty.');
        }

        foreach (self::cases() as $case) {
            if (strtolower($case->value) === $normalized) {
                return $case;
            }
        }

        // Backwards compatibility with legacy DigiKey API field names
        return match ($normalized) {
            'partnumber' => self::DigiKeyProductNumber,
            'manufacturerpartnumber' => self::ManufacturerProductNumber,
            default => throw new InvalidArgumentException("Unsupported sort field: {$value}"),
        };
    }
}

