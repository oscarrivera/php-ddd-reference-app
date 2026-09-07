<?php

declare(strict_types=1);

use Warehouse\Catalog\Application\AddProduct\AddProductCommand;
use Warehouse\Inventory\Application\ReserveStock\ReserveStockCommand;
use Warehouse\Inventory\Application\SetStock\SetStockCommand;
use Warehouse\Shared\Domain\DomainException;
use Warehouse\Shared\Infrastructure\AppFactory;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "Run composer install first.\n");
    exit(1);
}

require $autoload;

$app = AppFactory::sqlite($root . '/var/app.sqlite');
$command = $argv[1] ?? null;

try {
    match ($command) {
        'product:add' => productAdd($app, $argv),
        'stock:set' => stockSet($app, $argv),
        'stock:reserve' => stockReserve($app, $argv),
        default => usage(),
    };
} catch (DomainException $exception) {
    fwrite(STDERR, $exception->errorCode() . ': ' . $exception->getMessage() . "\n");
    exit(1);
}

function productAdd(AppFactory $app, array $argv): void
{
    $sku = $argv[2] ?? '';
    $name = $argv[3] ?? '';
    if ($sku === '' || $name === '') {
        fwrite(STDERR, "Usage: php bin/console.php product:add <sku> <name>\n");
        exit(1);
    }
    $product = $app->addProduct->handle(new AddProductCommand($sku, $name));
    fwrite(STDOUT, json_encode($product->toArray(), JSON_UNESCAPED_UNICODE) . "\n");
}

function stockSet(AppFactory $app, array $argv): void
{
    $sku = $argv[2] ?? '';
    $onHand = $argv[3] ?? '';
    if ($sku === '' || !preg_match('/^-?\d+$/', $onHand)) {
        fwrite(STDERR, "Usage: php bin/console.php stock:set <sku> <onHand>\n");
        exit(1);
    }
    $item = $app->setStock->handle(new SetStockCommand($sku, (int) $onHand));
    fwrite(STDOUT, json_encode($item->toArray(), JSON_UNESCAPED_UNICODE) . "\n");
}

function stockReserve(AppFactory $app, array $argv): void
{
    $sku = $argv[2] ?? '';
    $qty = $argv[3] ?? '';
    if ($sku === '' || !preg_match('/^-?\d+$/', $qty)) {
        fwrite(STDERR, "Usage: php bin/console.php stock:reserve <sku> <qty>\n");
        exit(1);
    }
    $event = $app->reserveStock->handle(new ReserveStockCommand($sku, (int) $qty));
    fwrite(STDOUT, json_encode($event->payload(), JSON_UNESCAPED_UNICODE) . "\n");
}

function usage(): never
{
    fwrite(STDOUT, <<<'TXT'
Warehouse CLI

  php bin/console.php product:add <sku> <name>
  php bin/console.php stock:set <sku> <onHand>
  php bin/console.php stock:reserve <sku> <qty>

TXT);
    exit(0);
}
