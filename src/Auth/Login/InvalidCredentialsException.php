<?php

declare(strict_types=1);

namespace App\Auth\Login;

use App\Support\Exception\DomainException;
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
