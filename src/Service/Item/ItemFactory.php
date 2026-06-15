<?php

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
        $item->setBonusDamage($offer->getBonusDamage());
        $item->setBonusCrit($offer->getBonusCrit());
        $item->setBonusHealth($offer->getBonusHealth());
        return $item;
    }

    public function rollBonusStats(ItemDefinition $definition): array
    {
        $bonusDamage = 0;
        $bonusCrit = 0;
        $bonusHealth = 0;

        if ($definition->getBaseDamage() > 0) {
            $randPercent = (mt_rand(-20, 20) / 100);
            $bonusDamage = (int)round($definition->getBaseDamage() * $randPercent);
        }

        if ($definition->getBaseCrit() > 0) {
            $randPercent = (mt_rand(-20, 20) / 100);
            $bonusCrit = (int)round($definition->getBaseCrit() * $randPercent);
        }

        if ($definition->getBaseHealth() > 0) {
            $randPercent = (mt_rand(-20, 20) / 100);
            $bonusHealth = (int)round($definition->getBaseHealth() * $randPercent);
        }

        return [$bonusDamage, $bonusCrit, $bonusHealth];
    }

}
