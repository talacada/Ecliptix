<?php

declare(strict_types=1);

namespace App\Auth\Registration;

use App\Support\Exception\DomainException;

class UsernameAlreadyTakenException extends DomainException
{
    public function __construct(
        private readonly ?string $username = null,
        string $message = 'Username already registered',
    ) {
        parent::__construct($message);
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
}
