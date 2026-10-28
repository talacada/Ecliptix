<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Character\ActiveElixir;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ActiveElixir>
 */
class ActiveElixirFactory extends PersistentObjectFactory
{
    protected function defaults(): array|callable
    {
        return [
            'character' => CharacterFactory::new(),
            'itemDefinition' => ItemDefinitionFactory::new(),
            'expires_at' => new \DateTimeImmutable('+ 1hours'),
        ];
    }

    public static function class(): string
    {
        return ActiveElixir::class;
    }
}
