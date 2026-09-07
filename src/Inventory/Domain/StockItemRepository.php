<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Domain;

use Warehouse\Catalog\Domain\Sku;

interface StockItemRepository
{
    public function save(StockItem $item): void;

    public function findBySku(Sku $sku): ?StockItem;
}
