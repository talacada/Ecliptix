<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Entity\AppearanceOption;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<AppearanceOption>
 */
final class AppearanceOptionFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return AppearanceOption::class;
    }

    protected function defaults(): array
    {
        return [
            'race' => RaceFactory::new(),
            'type' => self::faker()->randomElement(AppearanceTypeEnum::cases()),
            'label' => self::faker()->word(),
            'sort_order' => self::faker()->numberBetween(1, 10),
        ];
    }
}
