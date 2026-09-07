<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Application\AddProduct;

use Warehouse\Catalog\Domain\Exception\ProductAlreadyExistsException;
use Warehouse\Catalog\Domain\Product;
use Warehouse\Catalog\Domain\ProductId;
use Warehouse\Catalog\Domain\ProductRepository;
use Warehouse\Catalog\Domain\Sku;

final class AddProductHandler
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    public function handle(AddProductCommand $command): Product
    {
        $sku = Sku::fromString($command->sku);
        if ($this->products->findBySku($sku) !== null) {
            throw ProductAlreadyExistsException::withSku($sku);
        }

        $product = Product::register(ProductId::generate(), $sku, $command->name);
        $this->products->save($product);

        return $product;
    }
}
