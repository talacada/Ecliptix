<?php

declare(strict_types=1);

namespace App\Auth\ChangePassword;

use App\Support\Exception\DomainException;

class InvalidPasswordException extends DomainException
{
    public function __construct(string $message = 'Password is incorrect')
    {
        parent::__construct($message);
    }
}
