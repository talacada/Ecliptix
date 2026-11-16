<?php

declare(strict_types=1);

namespace App\Auth\ChangePassword;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Character\Character\Character;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/auth/change-password',
            normalizationContext: ['groups' => [Character::READ_GROUP]],
            security: 'is_granted("ROLE_USER")',
            input: ChangePasswordInput::class,
            output: Character::class,
            processor: ChangePasswordProcessor::class,
        ),
    ],
)]
class ChangePasswordInput
{
    public function __construct(string $oldPassword, string $newPassword)
    {
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
