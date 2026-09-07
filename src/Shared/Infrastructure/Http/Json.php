<?php

declare(strict_types=1);

namespace Warehouse\Shared\Infrastructure\Http;

use JsonException;
use Warehouse\Shared\Domain\DomainException;

final class Json
{
    /** @return array<string, mixed> */
    public static function decodeBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            throw new DomainException('Request body is required.', 'INVALID_JSON');
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new DomainException('Request body must be valid JSON.', 'INVALID_JSON');
        }

        if (!is_array($data) || ($data !== [] && array_is_list($data))) {
            throw new DomainException('Request body must be a JSON object.', 'INVALID_JSON');
        }

        return $data;
    }

    /** @param array<string, mixed> $payload */
    public static function send(int $status, array $payload): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
