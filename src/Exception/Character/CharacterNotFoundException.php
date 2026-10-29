<?php

declare(strict_types=1);

namespace App\Exception\Character;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_NOT_FOUND)]
class CharacterNotFoundException extends DomainException
{
    public function __construct(
        private readonly int|null $identifier = null,
        string $message = 'Character not found',
    ) {
        parent::__construct($message);
    }

    public function getIdentifier(): int|string|null
    {
        return $this->identifier;
    }
}
