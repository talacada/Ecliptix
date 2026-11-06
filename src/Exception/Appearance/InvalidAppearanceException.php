<?php

namespace App\Exception\Appearance;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

class InvalidAppearanceException extends DomainException
{
    private readonly string $name;

    public function __construct(string $name, string $message = 'Invalid ')
    {
        parent::__construct($message . $name);
    }

}
