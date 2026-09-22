<?php

namespace App\Factory;

use App\Entity\PasswordResetToken;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<PasswordResetToken>
 */
final class PasswordResetTokenFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return PasswordResetToken::class;
    }

    protected function defaults(): array
    {
        return [
            'character' => CharacterFactory::new(),
            'token' => Uuid::v4(),
            'expires_at' => new DateTimeImmutable('+1 hour'),
            'used_at' => null,
        ];
    }
}
