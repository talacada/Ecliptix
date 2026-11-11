<?php

declare(strict_types=1);

namespace App\Character\Appearance;

use App\Support\Exception\DomainException;

class InvalidAppearanceException extends DomainException
{
    public function __construct(
        private readonly string $name,
        string $message = 'Invalid ',
    ) {
        parent::__construct($message.$name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
