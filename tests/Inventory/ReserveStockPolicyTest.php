<?php

declare(strict_types=1);

namespace Warehouse\Tests\Inventory;

use PHPUnit\Framework\TestCase;
use Warehouse\Inventory\Domain\Exception\InsufficientStockException;
use Warehouse\Inventory\Domain\Exception\InvalidQuantityException;
use Warehouse\Inventory\Domain\ReserveStockPolicy;

final class ReserveStockPolicyTest extends TestCase
{
    public function testAvailableIsOnHandMinusReserved(): void
    {
        $policy = new ReserveStockPolicy();

        self::assertSame(7, $policy->available(10, 3));
    }

    public function testItAllowsReservationWithinAvailability(): void
    {
        $this->expectNotToPerformAssertions();
        (new ReserveStockPolicy())->assertSatisfied(10, 3, 7);
    }

    public function testItRejectsReservationAboveAvailability(): void
    {
        $this->expectException(InsufficientStockException::class);
        (new ReserveStockPolicy())->assertSatisfied(10, 3, 8);
    }

    public function testItRejectsZeroQuantity(): void
    {
        $this->expectException(InvalidQuantityException::class);
        (new ReserveStockPolicy())->assertSatisfied(10, 0, 0);
    }

    public function testItRejectsNegativeQuantity(): void
    {
        $this->expectException(InvalidQuantityException::class);
        (new ReserveStockPolicy())->assertSatisfied(10, 0, -1);
    }
}
