<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Application\AddProduct;

final class AddProductCommand
{
    public function __construct(
        public readonly string $sku,
        public readonly string $name,
    ) {
    }
}
