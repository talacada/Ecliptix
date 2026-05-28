<?php

namespace App\ApiResource\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordInput
{
    public function __construct($oldPassword, $newPassword) {
        $this->oldPassword = $oldPassword;
        $this->newPassword = $newPassword;
    }

    #[Assert\NotBlank]
    public string $oldPassword;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    public string $newPassword;

    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }


}
