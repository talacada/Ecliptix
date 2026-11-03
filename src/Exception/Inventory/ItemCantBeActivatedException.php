<?php

declare(strict_types=1);

namespace App\Exception\Inventory;

use App\Entity\Item\ItemDefinition;
use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class ItemCantBeActivatedException extends DomainException
{
    public function __construct(string $message = 'This item cannot be activated', ?ItemDefinition $itemDefinition = null)
    {
        parent::__construct($message);
    }
}
