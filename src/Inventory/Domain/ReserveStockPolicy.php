<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Domain;

use Warehouse\Inventory\Domain\Exception\InsufficientStockException;
use Warehouse\Inventory\Domain\Exception\InvalidQuantityException;

final class ReserveStockPolicy
{
    public function available(int $onHand, int $reserved): int
    {
        return $onHand - $reserved;
    }

    public function assertSatisfied(int $onHand, int $reserved, int $qty): void
    {
        if ($qty < 1) {
            throw new InvalidQuantityException('Quantity must be a positive integer.', 'INVALID_QUANTITY');
        }

        $available = $this->available($onHand, $reserved);
        if ($qty > $available) {
            throw new InsufficientStockException(
                sprintf('Cannot reserve %d units; only %d available.', $qty, $available),
                'INSUFFICIENT_STOCK',
            );
        }
    }
}
