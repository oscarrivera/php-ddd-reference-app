<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Infrastructure\Persistence;

use PDO;
use Warehouse\Catalog\Domain\Sku;
use Warehouse\Inventory\Domain\StockItem;
use Warehouse\Inventory\Domain\StockItemRepository;

final class SqliteStockItemRepository implements StockItemRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function save(StockItem $item): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO stock_items (sku, on_hand, reserved) VALUES (:sku, :on_hand, :reserved)
             ON CONFLICT(sku) DO UPDATE SET on_hand = excluded.on_hand, reserved = excluded.reserved',
        );
        $statement->execute([
            'sku' => $item->sku()->toString(),
            'on_hand' => $item->onHand(),
            'reserved' => $item->reserved(),
        ]);
    }

    public function findBySku(Sku $sku): ?StockItem
    {
        $statement = $this->pdo->prepare('SELECT sku, on_hand, reserved FROM stock_items WHERE sku = :sku');
        $statement->execute(['sku' => $sku->toString()]);
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }

        return StockItem::reconstitute(
            Sku::fromString($row['sku']),
            (int) $row['on_hand'],
            (int) $row['reserved'],
        );
    }
}
