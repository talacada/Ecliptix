<?php

declare(strict_types=1);

namespace App\Exception\Friend;

use App\Exception\DomainException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_BAD_REQUEST)]
class AlreadyFriendsException extends DomainException
{
    public function __construct(string $message = 'You are already friends')
    {
        parent::__construct($message);
    }
}
