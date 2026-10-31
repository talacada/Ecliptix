<?php

namespace App\Exception\Auth;

use App\Exception\DomainException;

class EmailAlreadyRegisteredException extends DomainException
{
    public function __construct(
        private readonly ?string $email = null,
        string $message = 'Email already registered',
    ) {
        parent::__construct($message);
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
