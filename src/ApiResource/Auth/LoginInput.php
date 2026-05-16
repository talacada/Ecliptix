<?php

namespace App\ApiResource\Auth;

use Symfony\Component\Validator\Constraints as Assert;

readonly class LoginInput
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
