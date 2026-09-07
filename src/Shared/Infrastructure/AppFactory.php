<?php

declare(strict_types=1);

namespace Warehouse\Shared\Infrastructure;

use Warehouse\Catalog\Application\AddProduct\AddProductHandler;
use Warehouse\Catalog\Application\ListProducts\ListProductsHandler;
use Warehouse\Catalog\Infrastructure\Persistence\InMemoryProductRepository;
use Warehouse\Catalog\Infrastructure\Persistence\SqliteProductRepository;
use Warehouse\Inventory\Application\ReserveStock\ReserveStockHandler;
use Warehouse\Inventory\Application\SetStock\SetStockHandler;
use Warehouse\Inventory\Infrastructure\Persistence\InMemoryStockItemRepository;
use Warehouse\Inventory\Infrastructure\Persistence\SqliteStockItemRepository;
use Warehouse\Shared\Infrastructure\Clock\SystemClock;
use Warehouse\Shared\Infrastructure\Persistence\SqliteDatabase;

final class AppFactory
{
    public function __construct(
        public readonly AddProductHandler $addProduct,
        public readonly ListProductsHandler $listProducts,
        public readonly SetStockHandler $setStock,
        public readonly ReserveStockHandler $reserveStock,
    ) {
    }

    public static function sqlite(string $databasePath): self
    {
        $pdo = SqliteDatabase::connect($databasePath);
        $clock = new SystemClock();
        $products = new SqliteProductRepository($pdo);
        $stock = new SqliteStockItemRepository($pdo);

        return new self(
            new AddProductHandler($products),
            new ListProductsHandler($products),
            new SetStockHandler($products, $stock),
            new ReserveStockHandler($products, $stock, $clock),
        );
    }

    public static function inMemory(): self
    {
        $clock = new SystemClock();
        $products = new InMemoryProductRepository();
        $stock = new InMemoryStockItemRepository();

        return new self(
            new AddProductHandler($products),
            new ListProductsHandler($products),
            new SetStockHandler($products, $stock),
            new ReserveStockHandler($products, $stock, $clock),
        );
    }
}
