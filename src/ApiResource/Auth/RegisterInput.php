<?php

declare(strict_types=1);

namespace App\ApiResource\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterInput
{
    #[Assert\NotBlank(message: 'Username should not be blank.')]
    #[Assert\Length(min: 4, max: 20, minMessage: 'Username must be at least {{ limit }} characters long.', maxMessage: 'Username cannot be longer than {{ limit }} characters.')]
    private string $username;

    #[Assert\NotBlank(message: 'Email should not be blank.')]
    #[Assert\Email(message: 'Email should be a valid email address.')]
    private string $email;

    #[Assert\NotBlank(message: 'Password should not be blank.')]
    #[Assert\Length(min: 8, minMessage: 'Password must be at least {{ limit }} characters long.')]
    private string $password;

    #[Assert\NotBlank(message: 'Race should not be blank.')]
    private int $race_id;

    #[Assert\NotBlank(message: 'Hair should not be blank.')]
    private int $hair_id;

    //TODO others

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}
