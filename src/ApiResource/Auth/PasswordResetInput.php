<?php

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\State\Processor\Auth\PasswordResetProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/password-reset',
            input: PasswordResetInput::class,
            processor: PasswordResetProcessor::class
        )
    ]
)]
class PasswordResetInput
{
    #[Assert\NotBlank(message: 'Password should not be blank.')]
    #[Assert\Length(min: 8, minMessage: 'Password must be at least {{ limit }} characters long.')]
    private string $password;

    #[Assert\NotBlank(message: 'Token should not be blank.')]
    #[Assert\Uuid(message: 'Token is not valid')]
    private string $token;


    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }


}
