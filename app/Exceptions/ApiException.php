<?php

namespace App\Exceptions;

use App\Enums\ApiErrorCode;
use Exception;
use Symfony\Component\HttpFoundation\Response;

final class ApiException extends Exception
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message,
        public readonly ApiErrorCode $errorCode,
        public readonly int $statusCode = Response::HTTP_BAD_REQUEST,
        public readonly array $details = [],
    ) {
        parent::__construct($message, $statusCode);
    }

    public static function notFound(string $message = 'Resource not found'): self
    {
        return new self($message, ApiErrorCode::RESOURCE_NOT_FOUND, Response::HTTP_NOT_FOUND);
    }

    public static function conflict(string $message = 'Resource already exists'): self
    {
        return new self($message, ApiErrorCode::CONFLICT, Response::HTTP_CONFLICT);
    }
}
