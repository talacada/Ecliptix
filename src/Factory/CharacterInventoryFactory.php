<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Character\CharacterInventory;
use App\Entity\Item\InventoryContainerEnum;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<CharacterInventory>
 */
class CharacterInventoryFactory extends PersistentObjectFactory
{
    protected function defaults(): array|callable
    {
        return [
            'character' => CharacterFactory::createOne(),
            'item' => ItemFactory::createOne(),
            'container' => InventoryContainerEnum::Backpack,
            'quantity' => 1,
            'position' => 0,
        ];
    }

    public static function class(): string
    {
        return CharacterInventory::class;
    }
}
