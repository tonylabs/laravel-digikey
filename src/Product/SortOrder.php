<?php

namespace TONYLABS\DigiKey\Product;

use InvalidArgumentException;

enum SortOrder: string
{
    case Ascending = 'Ascending';
    case Descending = 'Descending';

    /**
     * Resolve a sort order from a case-insensitive string.
     */
    public static function fromValue(string $value): self
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'asc', 'ascending' => self::Ascending,
            'desc', 'descending' => self::Descending,
            default => throw new InvalidArgumentException('Sort direction must be Ascending or Descending.'),
        };
    }
}

