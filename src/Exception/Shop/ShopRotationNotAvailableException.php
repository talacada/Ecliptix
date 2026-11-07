<?php

declare(strict_types=1);

namespace App\Exception\Shop;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class ShopRotationNotAvailableException extends DomainException
{
    public function __construct(string $message = 'Rotation not available')
    {
        parent::__construct($message);
    }
}
