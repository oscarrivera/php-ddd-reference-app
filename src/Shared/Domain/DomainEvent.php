<?php

declare(strict_types=1);

namespace Warehouse\Shared\Domain;

use DateTimeImmutable;

interface DomainEvent
{
    public function eventName(): string;

    public function occurredOn(): DateTimeImmutable;

    /** @return array<string, mixed> */
    public function payload(): array;
}
