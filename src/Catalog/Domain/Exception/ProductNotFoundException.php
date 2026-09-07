<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Domain\Exception;

use Warehouse\Catalog\Domain\Sku;
use Warehouse\Shared\Domain\DomainException;

final class ProductNotFoundException extends DomainException
{
    public static function withSku(Sku $sku): self
    {
        return new self(
            sprintf('Product with SKU %s was not found.', $sku->toString()),
            'PRODUCT_NOT_FOUND',
        );
    }
}
