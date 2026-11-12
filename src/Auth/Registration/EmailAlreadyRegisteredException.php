<?php

declare(strict_types=1);

namespace App\Auth\Registration;

use App\Support\Exception\DomainException;

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
