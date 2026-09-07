<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Domain\Exception;

use Warehouse\Catalog\Domain\Sku;
use Warehouse\Shared\Domain\DomainException;

final class ProductAlreadyExistsException extends DomainException
{
    public static function withSku(Sku $sku): self
    {
        return new self(
            sprintf('Product with SKU %s already exists.', $sku->toString()),
            'PRODUCT_ALREADY_EXISTS',
        );
    }
}
