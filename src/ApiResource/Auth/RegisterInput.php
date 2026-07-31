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
    #[Assert\Positive(message: 'Race must be a positive integer.')]
    private int $race_id;

    #[Assert\NotBlank(message: 'Hair should not be blank.')]
    #[Assert\Positive(message: 'Hair must be a positive integer.')]
    private int $hair_id;

    #[Assert\NotBlank(message: 'Eyes should not be blank.')]
    #[Assert\Positive(message: 'Eyes must be a positive integer.')]
    private int $eyes_id;

    #[Assert\NotBlank(message: 'Mouth should not be blank.')]
    #[Assert\Positive(message: 'Mouth must be a positive integer.')]
    private int $mouth_id;

    #[Assert\NotBlank(message: 'Nose should not be blank.')]
    #[Assert\Positive(message: 'Nose must be a positive integer.')]
    private int $nose_id;

    #[Assert\NotBlank(message: 'Ears should not be blank.')]
    #[Assert\Positive(message: 'Ears must be a positive integer.')]
    private int $ears_id;

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

    public function getRaceId(): int
    {
        return $this->race_id;
    }

    public function setRaceId(int $race_id): void
    {
        $this->race_id = $race_id;
    }

    public function getHairId(): int
    {
        return $this->hair_id;
    }

    public function setHairId(int $hair_id): void
    {
        $this->hair_id = $hair_id;
    }

    public function getEyesId(): int
    {
        return $this->eyes_id;
    }

    public function setEyesId(int $eyes_id): void
    {
        $this->eyes_id = $eyes_id;
    }

    public function getMouthId(): int
    {
        return $this->mouth_id;
    }

    public function setMouthId(int $mouth_id): void
    {
        $this->mouth_id = $mouth_id;
    }

    public function getNoseId(): int
    {
        return $this->nose_id;
    }

    public function setNoseId(int $nose_id): void
    {
        $this->nose_id = $nose_id;
    }

    public function getEarsId(): int
    {
        return $this->ears_id;
    }

    public function setEarsId(int $ears_id): void
    {
        $this->ears_id = $ears_id;
    }


}
