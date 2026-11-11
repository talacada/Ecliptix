<?php

declare(strict_types=1);

namespace App\Exception\Inventory;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class InvalidInventoryOperationException extends DomainException
{
    public function __construct(string $message = 'Invalid inventory operation.')
    {
        parent::__construct($message);
    }
}
