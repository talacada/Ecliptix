<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Entity\Character\Character;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Character>
 */
final class CharacterFactory extends PersistentObjectFactory
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    public static function class(): string
    {
        return Character::class;
    }

    protected function defaults(): array
    {
        $race = RaceFactory::new();

        return [
            'email' => self::faker()->unique()->safeEmail(),
            'username' => self::faker()->unique()->regexify('[A-Za-z0-9]{6,15}'),
            'passwordHash' => $this->passwordHasher->hashPassword(new Character(), 'password123'),
            'email_verified' => true,
            'race' => $race,
            'hair' => AppearanceOptionFactory::new(['race' => $race, 'type' => AppearanceTypeEnum::hair]),
            'eyes' => AppearanceOptionFactory::new(['race' => $race, 'type' => AppearanceTypeEnum::eyes]),
            'mouth' => AppearanceOptionFactory::new(['race' => $race, 'type' => AppearanceTypeEnum::mouth]),
            'nose' => AppearanceOptionFactory::new(['race' => $race, 'type' => AppearanceTypeEnum::nose]),
            'ears' => AppearanceOptionFactory::new(['race' => $race, 'type' => AppearanceTypeEnum::ears]),
        ];
    }

    public function unverified(): self
    {
        return $this->with(['email_verified' => false]);
    }
}
