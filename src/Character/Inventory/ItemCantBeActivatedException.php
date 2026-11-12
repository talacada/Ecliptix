<?php

declare(strict_types=1);

namespace App\Character\Inventory;

use App\Item\Definition\ItemDefinition;
use App\Support\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class ItemCantBeActivatedException extends DomainException
{
    public function __construct(
        string $message = 'This item cannot be activated',
        private readonly ?ItemDefinition $itemDefinition = null,
    ) {
        parent::__construct($message);
    }

    public function getItemDefinition(): ?ItemDefinition
    {
        return $this->itemDefinition;
    }
}
