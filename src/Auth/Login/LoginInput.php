<?php

declare(strict_types=1);

namespace App\Auth\Login;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/login',
            normalizationContext: ['groups' => [LoginOutput::READ_GROUP]],
            input: LoginInput::class,
            output: LoginOutput::class,
            processor: LoginProcessor::class,
        ),
    ]
)]
class LoginInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        private string $email,

        #[Assert\NotBlank]
        private string $password,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
