<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Application\ReserveStock;

final class ReserveStockCommand
{
    public function __construct(
        public readonly string $sku,
        public readonly int $qty,
    ) {
    }
}
