<?php

declare(strict_types=1);

namespace App\Exception\Character;

use App\Exception\DomainException;

class InsufficientGoldException extends DomainException
{
    public function __construct(
        private readonly int $required = 0,
        private readonly int $available = 0,
        string $message = 'Not enough gold',
    ) {
        parent::__construct($message);
    }

    public function getRequired(): int
    {
        return $this->required;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }
}
