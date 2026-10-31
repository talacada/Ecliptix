<?php

namespace App\Exception\Auth;

namespace App\Exception\Auth;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_UNPROCESSABLE_ENTITY)]
class InvalidTokenException extends DomainException
{
    public function __construct(string $message = 'Invalid or expired token.')
    {
        parent::__construct($message);
    }
}
