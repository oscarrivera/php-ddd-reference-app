<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Application\SetStock;

use Warehouse\Catalog\Domain\Exception\ProductNotFoundException;
use Warehouse\Catalog\Domain\ProductRepository;
use Warehouse\Catalog\Domain\Sku;
use Warehouse\Inventory\Domain\StockItem;
use Warehouse\Inventory\Domain\StockItemRepository;

final class SetStockHandler
{
    public function __construct(
        private readonly ProductRepository $products,
        private readonly StockItemRepository $stock,
    ) {
    }

    public function handle(SetStockCommand $command): StockItem
    {
        $sku = Sku::fromString($command->sku);
        if ($this->products->findBySku($sku) === null) {
            throw ProductNotFoundException::withSku($sku);
        }

        $item = $this->stock->findBySku($sku);
        if ($item === null) {
            $item = StockItem::set($sku, $command->onHand);
        } else {
            $item->replaceOnHand($command->onHand);
        }

        $this->stock->save($item);

        return $item;
    }
}
