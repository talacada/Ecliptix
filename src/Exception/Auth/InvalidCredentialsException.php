<?php

declare(strict_types=1);

namespace App\Exception\Auth;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_UNAUTHORIZED)]
class InvalidCredentialsException extends DomainException
{
    public function __construct(string $message = 'Invalid credentials')
    {
        parent::__construct($message);
    }
}
