<?php

declare(strict_types=1);

namespace Warehouse\Shared\Domain;

use RuntimeException;
use Throwable;

class DomainException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $errorCode = 'DOMAIN_ERROR',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
