<?php

namespace App\Factory;

use App\Entity\Item\Item;
use App\Entity\Shop\ShopOffer;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

class ItemFactory extends PersistentObjectFactory
{

    public function createFromDefinitionAndOffer(ShopOffer $offer): Item
    {
        $item = new Item();

        $item->setDefinition($offer->getItemDefinition());
        $item->setBonusDamage($offer->getBonusDamage() ?? 0);
        $item->setBonusCrit($offer->getBonusCrit() ?? 0);
        $item->setBonusHealth($offer->getBonusHealth() ?? 0);

        return $item;
    }

    protected function defaults(): array|callable
    {
        return [
            'definition' => ItemDefinitionFactory::createOne(),
            'bonusDamage' => self::faker()->numberBetween(1, 50),
            'bonusHealth' => self::faker()->numberBetween(1, 50),
            'bonusCrit' => self::faker()->numberBetween(1, 50),
        ];
    }

    public static function class(): string
    {
        return Item::class;
    }
}
