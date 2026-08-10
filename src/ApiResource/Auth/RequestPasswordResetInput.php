<?php

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\Processor\Auth\RequestPasswordResetProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/request-password-reset',
            input: RequestPasswordResetInput::class,
            processor: RequestPasswordResetProcessor::class
        )
    ]
)]
class RequestPasswordResetInput
{
    #[Assert\NotBlank(message: 'Email should not be blank.')]
    #[Assert\Email(message: 'Email should be a valid email address.')]
    private string $email;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }


}
