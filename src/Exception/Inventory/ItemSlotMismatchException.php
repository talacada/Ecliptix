<?php

declare(strict_types=1);

namespace App\Exception\Inventory;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class ItemSlotMismatchException extends DomainException
{
    public function __construct(string $message = 'Items does not match slots.')
    {
        parent::__construct($message);
    }
}
