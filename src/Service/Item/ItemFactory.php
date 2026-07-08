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

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public function rollBonusStats(ItemDefinition $definition): array
    {
        $bonusDamage = 0;
        $bonusCrit = 0;
        $bonusHealth = 0;

        if ($definition->getBaseDamage() > 0) {
            $randPercent = (mt_rand(-20, 20) / 100);
            $bonusDamage = (int) round($definition->getBaseDamage() * $randPercent);
        }

        if ($definition->getBaseCrit() > 0) {
            $randPercent = (mt_rand(-20, 20) / 100);
            $bonusCrit = (int) round($definition->getBaseCrit() * $randPercent);
        }

        if ($definition->getBaseHealth() > 0) {
            $randPercent = (mt_rand(-20, 20) / 100);
            $bonusHealth = (int) round($definition->getBaseHealth() * $randPercent);
        }

        return [$bonusDamage, $bonusCrit, $bonusHealth];
    }
}
