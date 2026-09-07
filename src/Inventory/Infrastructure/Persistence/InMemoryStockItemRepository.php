<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Infrastructure\Persistence;

use Warehouse\Catalog\Domain\Sku;
use Warehouse\Inventory\Domain\StockItem;
use Warehouse\Inventory\Domain\StockItemRepository;

final class InMemoryStockItemRepository implements StockItemRepository
{
    /** @var array<string, StockItem> */
    private array $items = [];

    public function save(StockItem $item): void
    {
        $this->items[$item->sku()->toString()] = $item;
    }

    public function findBySku(Sku $sku): ?StockItem
    {
        return $this->items[$sku->toString()] ?? null;
    }
}
