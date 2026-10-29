<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;
use Throwable;

/**
 * Base domain exception representing a business rule violation.
 * Defaults to HTTP 422.
 *
 * Child exceptions automatically inherit the HTTP 422 status unless
 * they override it with their own #[WithHttpStatus(...)] attribute.
 */
#[WithHttpStatus(Response::HTTP_UNPROCESSABLE_ENTITY)]
abstract class DomainException extends \DomainException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
