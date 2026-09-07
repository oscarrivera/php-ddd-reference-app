<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Application\ListProducts;

use Warehouse\Catalog\Domain\ProductRepository;

final class ListProductsHandler
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    /** @return list<array{id: string, sku: string, name: string, active: bool}> */
    public function handle(): array
    {
        $items = [];
        foreach ($this->products->all() as $product) {
            $items[] = $product->toArray();
        }

        return $items;
    }
}
