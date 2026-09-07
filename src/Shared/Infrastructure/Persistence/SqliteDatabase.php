<?php

declare(strict_types=1);

namespace Warehouse\Shared\Infrastructure\Persistence;

use PDO;
use RuntimeException;

final class SqliteDatabase
{
    public static function connect(string $path): PDO
    {
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create SQLite directory: ' . $directory);
        }

        $pdo = new PDO('sqlite:' . $path, options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec(
            <<<'SQL'
            CREATE TABLE IF NOT EXISTS products (
                id TEXT PRIMARY KEY,
                sku TEXT NOT NULL UNIQUE,
                name TEXT NOT NULL,
                active INTEGER NOT NULL DEFAULT 1
            );

            CREATE TABLE IF NOT EXISTS stock_items (
                sku TEXT PRIMARY KEY,
                on_hand INTEGER NOT NULL,
                reserved INTEGER NOT NULL DEFAULT 0
            );
            SQL
        );

        return $pdo;
    }
}
