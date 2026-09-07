<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Domain;

use Warehouse\Catalog\Domain\Exception\InvalidProductNameException;
use Warehouse\Shared\Domain\AggregateRoot;

final class Product extends AggregateRoot
{
    private function __construct(
        private readonly ProductId $id,
        private readonly Sku $sku,
        private string $name,
        private bool $active,
    ) {
    }

    public static function register(ProductId $id, Sku $sku, string $name): self
    {
        return new self($id, $sku, self::assertName($name), true);
    }

    public static function reconstitute(ProductId $id, Sku $sku, string $name, bool $active): self
    {
        return new self($id, $sku, self::assertName($name), $active);
    }

    public function id(): ProductId
    {
        return $this->id;
    }

    public function sku(): Sku
    {
        return $this->sku;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function rename(string $name): void
    {
        $this->name = self::assertName($name);
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    /** @return array{id: string, sku: string, name: string, active: bool} */
    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'sku' => $this->sku->toString(),
            'name' => $this->name,
            'active' => $this->active,
        ];
    }

    private static function assertName(string $name): string
    {
        $normalized = trim($name);
        $length = mb_strlen($normalized);
        if ($length < 1 || $length > 120) {
            throw new InvalidProductNameException(
                'Product name must be between 1 and 120 characters.',
                'INVALID_PRODUCT_NAME',
            );
        }

        return $normalized;
    }
}
