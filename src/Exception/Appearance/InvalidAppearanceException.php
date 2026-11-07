<?php

namespace App\Exception\Appearance;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

class InvalidAppearanceException extends DomainException
{
    public function __construct(
        private readonly string $name,
        string $message = 'Invalid ',
    ) {
        parent::__construct($message . $name);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
