<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Domain;

use Warehouse\Catalog\Domain\Sku;
use Warehouse\Inventory\Domain\Event\StockReserved;
use Warehouse\Shared\Domain\AggregateRoot;
use Warehouse\Shared\Domain\Clock;
use Warehouse\Shared\Domain\DomainException;

final class StockItem extends AggregateRoot
{
    private function __construct(
        private readonly Sku $sku,
        private int $onHand,
        private int $reserved,
    ) {
        $this->assertCounts($onHand, $reserved);
    }

    public static function set(Sku $sku, int $onHand): self
    {
        return new self($sku, $onHand, 0);
    }

    public static function reconstitute(Sku $sku, int $onHand, int $reserved): self
    {
        return new self($sku, $onHand, $reserved);
    }

    public function sku(): Sku
    {
        return $this->sku;
    }

    public function onHand(): int
    {
        return $this->onHand;
    }

    public function reserved(): int
    {
        return $this->reserved;
    }

    public function available(): int
    {
        return (new ReserveStockPolicy())->available($this->onHand, $this->reserved);
    }

    public function replaceOnHand(int $onHand): void
    {
        $this->assertCounts($onHand, $this->reserved);
        $this->onHand = $onHand;
    }

    public function reserve(int $qty, Clock $clock): StockReserved
    {
        (new ReserveStockPolicy())->assertSatisfied($this->onHand, $this->reserved, $qty);
        $this->reserved += $qty;
        $event = new StockReserved(
            $this->sku,
            $qty,
            $this->onHand,
            $this->reserved,
            $clock->now(),
        );
        $this->record($event);

        return $event;
    }

    /** @return array{sku: string, onHand: int, reserved: int, available: int} */
    public function toArray(): array
    {
        return [
            'sku' => $this->sku->toString(),
            'onHand' => $this->onHand,
            'reserved' => $this->reserved,
            'available' => $this->available(),
        ];
    }

    private function assertCounts(int $onHand, int $reserved): void
    {
        if ($onHand < 0 || $reserved < 0) {
            throw new DomainException('On-hand and reserved quantities cannot be negative.', 'INVALID_ON_HAND');
        }
        if ($reserved > $onHand) {
            throw new DomainException('Reserved quantity cannot exceed on-hand stock.', 'INVALID_ON_HAND');
        }
    }
}
