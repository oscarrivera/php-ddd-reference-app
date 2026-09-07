<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Domain\Exception;

use Warehouse\Catalog\Domain\Sku;
use Warehouse\Shared\Domain\DomainException;

final class StockItemNotFoundException extends DomainException
{
    public static function withSku(Sku $sku): self
    {
        return new self(
            sprintf('Stock item for SKU %s was not found.', $sku->toString()),
            'STOCK_NOT_FOUND',
        );
    }
}
