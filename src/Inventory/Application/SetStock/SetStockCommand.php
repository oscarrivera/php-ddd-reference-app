<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Application\SetStock;

final class SetStockCommand
{
    public function __construct(
        public readonly string $sku,
        public readonly int $onHand,
    ) {
    }
}
