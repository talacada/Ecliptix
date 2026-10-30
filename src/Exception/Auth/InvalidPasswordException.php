<?php

declare(strict_types=1);

namespace App\Exception\Auth;

use App\Exception\DomainException;

class InvalidPasswordException extends DomainException
{
    public function __construct(string $message = 'Password is incorrect')
    {
        parent::__construct($message);
    }
}
