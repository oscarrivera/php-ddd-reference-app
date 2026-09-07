<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Infrastructure\Persistence;

use Warehouse\Catalog\Domain\Product;
use Warehouse\Catalog\Domain\ProductRepository;
use Warehouse\Catalog\Domain\Sku;

final class InMemoryProductRepository implements ProductRepository
{
    /** @var array<string, Product> */
    private array $items = [];

    public function save(Product $product): void
    {
        $this->items[$product->sku()->toString()] = $product;
    }

    public function findBySku(Sku $sku): ?Product
    {
        return $this->items[$sku->toString()] ?? null;
    }

    public function all(): array
    {
        return array_values($this->items);
    }
}
