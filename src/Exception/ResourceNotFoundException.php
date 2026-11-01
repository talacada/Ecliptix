<?php

namespace App\Exception;

use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_NOT_FOUND)]
class ResourceNotFoundException extends DomainException
{
    private readonly string $resourceName;

    /**
     * @param class-string|string $resource Např. ActiveElixir::class nebo Character::class
     */
    public function __construct(
        string $resource = 'Resource',
        private readonly int|string|null $identifier = null,
        string $message = '',
    ) {
        $this->resourceName = class_exists($resource)
            ? (new ReflectionClass($resource))->getShortName()
            : $resource;

        if ('' === $message) {
            $message = null !== $this->identifier
                ? sprintf('%s with ID "%s" not found.', $this->resourceName, $this->identifier)
                : sprintf('%s not found.', $this->resourceName);
        }

        parent::__construct($message);
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function getIdentifier(): int|string|null
    {
        return $this->identifier;
    }
}
