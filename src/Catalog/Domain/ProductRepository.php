<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Domain;

interface ProductRepository
{
    public function save(Product $product): void;

    public function findBySku(Sku $sku): ?Product;

    /** @return list<Product> */
    public function all(): array;
}
