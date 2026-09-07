<?php

declare(strict_types=1);

namespace Warehouse\Tests\Inventory;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Warehouse\Catalog\Domain\Product;
use Warehouse\Catalog\Domain\ProductId;
use Warehouse\Catalog\Domain\Sku;
use Warehouse\Catalog\Infrastructure\Persistence\InMemoryProductRepository;
use Warehouse\Inventory\Application\ReserveStock\ReserveStockCommand;
use Warehouse\Inventory\Application\ReserveStock\ReserveStockHandler;
use Warehouse\Inventory\Application\SetStock\SetStockCommand;
use Warehouse\Inventory\Application\SetStock\SetStockHandler;
use Warehouse\Inventory\Domain\Exception\InsufficientStockException;
use Warehouse\Inventory\Domain\StockItem;
use Warehouse\Inventory\Infrastructure\Persistence\InMemoryStockItemRepository;
use Warehouse\Shared\Domain\Clock;

final class InMemoryStockItemRepositoryTest extends TestCase
{
    public function testSaveAndFindBySku(): void
    {
        $repository = new InMemoryStockItemRepository();
        $sku = Sku::fromString('SKU-9');
        $repository->save(StockItem::set($sku, 40));

        $found = $repository->findBySku($sku);

        self::assertNotNull($found);
        self::assertSame(40, $found->onHand());
        self::assertSame(0, $found->reserved());
    }

    public function testReserveUpdatesStoredAggregateAndEmitsEvent(): void
    {
        $products = new InMemoryProductRepository();
        $stock = new InMemoryStockItemRepository();
        $products->save(Product::register(ProductId::generate(), Sku::fromString('RES-1'), 'Resistor'));

        $set = new SetStockHandler($products, $stock);
        $set->handle(new SetStockCommand('RES-1', 10));

        $handler = new ReserveStockHandler($products, $stock, new FrozenClock());
        $event = $handler->handle(new ReserveStockCommand('RES-1', 4));

        $stored = $stock->findBySku(Sku::fromString('RES-1'));
        self::assertNotNull($stored);
        self::assertSame(4, $stored->reserved());
        self::assertSame(6, $stored->available());
        self::assertSame('inventory.stock_reserved', $event->eventName());
        self::assertSame(4, $event->payload()['qty']);
    }

    public function testReserveFailsWhenQuantityExceedsAvailability(): void
    {
        $products = new InMemoryProductRepository();
        $stock = new InMemoryStockItemRepository();
        $products->save(Product::register(ProductId::generate(), Sku::fromString('RES-2'), 'Capacitor'));
        (new SetStockHandler($products, $stock))->handle(new SetStockCommand('RES-2', 5));
        $handler = new ReserveStockHandler($products, $stock, new FrozenClock());
        $handler->handle(new ReserveStockCommand('RES-2', 5));

        $this->expectException(InsufficientStockException::class);
        $handler->handle(new ReserveStockCommand('RES-2', 1));
    }
}

final class FrozenClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-01-15T10:00:00+00:00');
    }
}
