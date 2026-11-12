<?php

declare(strict_types=1);

namespace App\Character\Inventory;

use App\Support\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class BackpackFullException extends DomainException
{
    public function __construct(string $message = 'Not enough backpack space')
    {
        parent::__construct($message);
    }
}
