<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Domain;

use Warehouse\Catalog\Domain\Exception\InvalidSkuException;

final class Sku
{
    private function __construct(private readonly string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));
        if (!preg_match('/^[A-Z0-9-]{3,32}$/', $normalized)) {
            throw new InvalidSkuException(
                'SKU must be 3-32 characters of A-Z, 0-9 or hyphen.',
                'INVALID_SKU',
            );
        }

        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
