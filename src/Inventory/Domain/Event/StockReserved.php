<?php

declare(strict_types=1);

namespace Warehouse\Inventory\Domain\Event;

use DateTimeImmutable;
use Warehouse\Catalog\Domain\Sku;
use Warehouse\Shared\Domain\DomainEvent;

final class StockReserved implements DomainEvent
{
    public function __construct(
        private readonly Sku $sku,
        private readonly int $qty,
        private readonly int $onHand,
        private readonly int $reserved,
        private readonly DateTimeImmutable $occurredOn,
    ) {
    }

    public function eventName(): string
    {
        return 'inventory.stock_reserved';
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function sku(): Sku
    {
        return $this->sku;
    }

    public function qty(): int
    {
        return $this->qty;
    }

    /** @return array{sku: string, qty: int, onHand: int, reserved: int, available: int, occurredOn: string} */
    public function payload(): array
    {
        return [
            'sku' => $this->sku->toString(),
            'qty' => $this->qty,
            'onHand' => $this->onHand,
            'reserved' => $this->reserved,
            'available' => $this->onHand - $this->reserved,
            'occurredOn' => $this->occurredOn->format(DATE_ATOM),
        ];
    }
}
