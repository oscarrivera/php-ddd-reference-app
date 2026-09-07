<?php

declare(strict_types=1);

namespace Warehouse\Shared\Infrastructure\Http;

use Throwable;
use Warehouse\Catalog\Application\AddProduct\AddProductCommand;
use Warehouse\Inventory\Application\ReserveStock\ReserveStockCommand;
use Warehouse\Inventory\Application\SetStock\SetStockCommand;
use Warehouse\Shared\Domain\DomainException;
use Warehouse\Shared\Infrastructure\AppFactory;

final class Kernel
{
    public function __construct(private readonly AppFactory $app)
    {
    }

    public function run(): void
    {
        try {
            $this->dispatch();
        } catch (DomainException $exception) {
            Json::send($this->statusFor($exception), [
                'error' => $exception->getMessage(),
                'code' => $exception->errorCode(),
            ]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            Json::send(500, [
                'error' => 'Internal server error.',
                'code' => 'INTERNAL_ERROR',
            ]);
        }
    }

    private function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = rtrim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
        $route = $method . ' ' . $path;

        match ($route) {
            'POST /products' => $this->addProduct(),
            'GET /products' => $this->listProducts(),
            'POST /stock/set' => $this->setStock(),
            'POST /stock/reserve' => $this->reserveStock(),
            default => Json::send(404, ['error' => 'Route not found.', 'code' => 'NOT_FOUND']),
        };
    }

    private function addProduct(): never
    {
        $body = Json::decodeBody();
        $sku = $this->stringField($body, 'sku');
        $name = $this->stringField($body, 'name');
        $product = $this->app->addProduct->handle(new AddProductCommand($sku, $name));
        Json::send(201, $product->toArray());
    }

    private function listProducts(): never
    {
        Json::send(200, ['items' => $this->app->listProducts->handle()]);
    }

    private function setStock(): never
    {
        $body = Json::decodeBody();
        $sku = $this->stringField($body, 'sku');
        $onHand = $this->intField($body, 'onHand');
        $item = $this->app->setStock->handle(new SetStockCommand($sku, $onHand));
        Json::send(200, $item->toArray());
    }

    private function reserveStock(): never
    {
        $body = Json::decodeBody();
        $sku = $this->stringField($body, 'sku');
        $qty = $this->intField($body, 'qty');
        $event = $this->app->reserveStock->handle(new ReserveStockCommand($sku, $qty));
        Json::send(200, $event->payload());
    }

    /** @param array<string, mixed> $body */
    private function stringField(array $body, string $field): string
    {
        $value = $body[$field] ?? null;
        if (!is_string($value) || trim($value) === '') {
            throw new DomainException(sprintf('Field "%s" is required.', $field), 'VALIDATION_ERROR');
        }

        return $value;
    }

    /** @param array<string, mixed> $body */
    private function intField(array $body, string $field): int
    {
        $value = $body[$field] ?? null;
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int) $value;
        }

        throw new DomainException(sprintf('Field "%s" must be an integer.', $field), 'VALIDATION_ERROR');
    }

    private function statusFor(DomainException $exception): int
    {
        return match ($exception->errorCode()) {
            'INSUFFICIENT_STOCK', 'PRODUCT_ALREADY_EXISTS' => 409,
            'PRODUCT_NOT_FOUND', 'STOCK_NOT_FOUND', 'NOT_FOUND' => 404,
            default => 422,
        };
    }
}
