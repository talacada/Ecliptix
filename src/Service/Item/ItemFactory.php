<?php

declare(strict_types=1);

namespace App\Service\Item;

use App\Entity\Item\Item;
use App\Entity\Item\ItemDefinition;
use App\Entity\Shop\ShopOffer;

class ItemFactory
{
    public function createFromDefinitionAndOffer(ItemDefinition $definition, ShopOffer $offer): Item
    {
        $item = new Item();

        $item->setDefinition($definition);
        $item->setBonusDamage($offer->getBonusDamage() ?? 0);
        $item->setBonusCrit($offer->getBonusCrit() ?? 0);
        $item->setBonusHealth($offer->getBonusHealth() ?? 0);

        return $item;
    }
}
