<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Race;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Race>
 */
final class RaceFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Race::class;
    }

    protected function defaults(): array
    {
        return [
            'name' => self::faker()->unique()->randomElement(['Human', 'Elf', 'Dwarf', 'Orc', 'Undead', 'Goblin', 'Gnome', 'Demon']),
        ];
    }
}
