<?php

declare(strict_types=1);

namespace Warehouse\Catalog\Infrastructure\Persistence;

use PDO;
use Warehouse\Catalog\Domain\Product;
use Warehouse\Catalog\Domain\ProductId;
use Warehouse\Catalog\Domain\ProductRepository;
use Warehouse\Catalog\Domain\Sku;

final class SqliteProductRepository implements ProductRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function save(Product $product): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO products (id, sku, name, active) VALUES (:id, :sku, :name, :active)
             ON CONFLICT(sku) DO UPDATE SET name = excluded.name, active = excluded.active',
        );
        $statement->execute([
            'id' => $product->id()->toString(),
            'sku' => $product->sku()->toString(),
            'name' => $product->name(),
            'active' => $product->isActive() ? 1 : 0,
        ]);
    }

    public function findBySku(Sku $sku): ?Product
    {
        $statement = $this->pdo->prepare('SELECT id, sku, name, active FROM products WHERE sku = :sku');
        $statement->execute(['sku' => $sku->toString()]);
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function all(): array
    {
        $rows = $this->pdo->query('SELECT id, sku, name, active FROM products ORDER BY sku')->fetchAll();
        $products = [];
        foreach ($rows as $row) {
            $products[] = $this->hydrate($row);
        }

        return $products;
    }

    /** @param array{id: string, sku: string, name: string, active: int|string} $row */
    private function hydrate(array $row): Product
    {
        return Product::reconstitute(
            ProductId::fromString($row['id']),
            Sku::fromString($row['sku']),
            $row['name'],
            (int) $row['active'] === 1,
        );
    }
}
