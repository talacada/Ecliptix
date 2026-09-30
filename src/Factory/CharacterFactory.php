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
        $race = RaceFactory::randomOrCreate();

        return [
            'email' => self::faker()->unique()->safeEmail(),
            'username' => self::faker()->unique()->regexify('[A-Za-z0-9]{6,15}'),
            'passwordHash' => $this->passwordHasher->hashPassword(new Character(), 'password123'),
            'email_verified' => true,
            'race' => $race,
            'hair' => AppearanceOptionFactory::randomOrCreate(['race' => $race, 'type' => AppearanceTypeEnum::hair]),
            'eyes' => AppearanceOptionFactory::randomOrCreate(['race' => $race, 'type' => AppearanceTypeEnum::eyes]),
            'mouth' => AppearanceOptionFactory::randomOrCreate(['race' => $race, 'type' => AppearanceTypeEnum::mouth]),
            'nose' => AppearanceOptionFactory::randomOrCreate(['race' => $race, 'type' => AppearanceTypeEnum::nose]),
            'ears' => AppearanceOptionFactory::randomOrCreate(['race' => $race, 'type' => AppearanceTypeEnum::ears]),
        ];
    }


    public function withPassword(string $plainPassword): self
    {
        return $this->with([
            'passwordHash' => $this->passwordHasher->hashPassword(new Character(), $plainPassword),
        ]);
    }
}
