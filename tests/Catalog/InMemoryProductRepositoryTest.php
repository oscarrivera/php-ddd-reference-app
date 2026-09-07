<?php

declare(strict_types=1);

namespace Warehouse\Tests\Catalog;

use PHPUnit\Framework\TestCase;
use Warehouse\Catalog\Application\AddProduct\AddProductCommand;
use Warehouse\Catalog\Application\AddProduct\AddProductHandler;
use Warehouse\Catalog\Application\ListProducts\ListProductsHandler;
use Warehouse\Catalog\Domain\Exception\ProductAlreadyExistsException;
use Warehouse\Catalog\Domain\Product;
use Warehouse\Catalog\Domain\ProductId;
use Warehouse\Catalog\Domain\Sku;
use Warehouse\Catalog\Infrastructure\Persistence\InMemoryProductRepository;

final class InMemoryProductRepositoryTest extends TestCase
{
    public function testSaveAndFindBySku(): void
    {
        $repository = new InMemoryProductRepository();
        $product = Product::register(ProductId::generate(), Sku::fromString('SKU-1'), 'Bolt');
        $repository->save($product);

        $found = $repository->findBySku(Sku::fromString('sku-1'));

        self::assertNotNull($found);
        self::assertSame('SKU-1', $found->sku()->toString());
        self::assertSame('Bolt', $found->name());
        self::assertTrue($found->isActive());
    }

    public function testAllReturnsSavedProducts(): void
    {
        $repository = new InMemoryProductRepository();
        $repository->save(Product::register(ProductId::generate(), Sku::fromString('AAA'), 'A'));
        $repository->save(Product::register(ProductId::generate(), Sku::fromString('BBB'), 'B'));

        self::assertCount(2, $repository->all());
    }

    public function testAddProductHandlerPersistsAndRejectsDuplicates(): void
    {
        $repository = new InMemoryProductRepository();
        $handler = new AddProductHandler($repository);
        $handler->handle(new AddProductCommand('WID-01', 'Widget'));

        $listed = (new ListProductsHandler($repository))->handle();
        self::assertCount(1, $listed);
        self::assertSame('WID-01', $listed[0]['sku']);

        $this->expectException(ProductAlreadyExistsException::class);
        $handler->handle(new AddProductCommand('wid-01', 'Other'));
    }
}
