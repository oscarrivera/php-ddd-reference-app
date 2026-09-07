<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Application\ReserveStock;

use Warehouse\Catalog\Domain\Exception\ProductNotFoundException;
use Warehouse\Catalog\Domain\ProductRepository;
use Warehouse\Catalog\Domain\Sku;
use Warehouse\Inventory\Domain\Event\StockReserved;
use Warehouse\Inventory\Domain\Exception\StockItemNotFoundException;
use Warehouse\Inventory\Domain\StockItemRepository;
use Warehouse\Shared\Domain\Clock;

final class ReserveStockHandler
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly StockItemRepository $stock,
        private readonly Clock $clock,
    ) {
    }

    public function handle(ReserveStockCommand $command): StockReserved
    {
        $sku = Sku::fromString($command->sku);
        if ($this->products->findBySku($sku) === null) {
            throw ProductNotFoundException::withSku($sku);
        }

        $item = $this->stock->findBySku($sku);
        if ($item === null) {
            throw StockItemNotFoundException::withSku($sku);
        }

        $event = $item->reserve($command->qty, $this->clock);
        $this->stock->save($item);

        return $event;
    }
}
