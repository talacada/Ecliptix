<?php

namespace App\Service\Item;

use App\Config\ItemConfig;
use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemRarityEnum;
use App\Entity\Item\ItemSlotEnum;

class ItemStatCalculator
{
    /**
     * Compute stats for a given slot, rarity, and level.
     *
     * Formula: (base + scaling * level) * rarityMultiplier
     *
     * @return array{float, float, float} [damage, crit, health]
     */
    public static function calculateStats(ItemSlotEnum $slot, ItemRarityEnum $rarity, int $level): array
    {
        $key = $slot->value;
        $multiplier = ItemConfig::RARITY_MULTIPLIER[$rarity->value];
        $scaling = ItemConfig::STAT_SCALING[$key];
        $base = ItemConfig::STAT_BASE[$key];

        $damage = ($base['damage'] + $scaling['damage'] * $level) * $multiplier;
        $crit = ($base['crit'] + $scaling['crit'] * $level) * $multiplier;
        $health = ($base['health'] + $scaling['health'] * $level) * $multiplier;

        return [$damage, $crit, $health];
    }

    /**
     * Compute prices from stats and rarity.
     *
     * Gold: sum of all stats * GOLD_PER_STAT_POINT since stats are scaled before.
     * Diamond: nonzero only for Epic+ rarity (10% of gold price).
     *
     * @return array{int, int} [goldPrice, diamondPrice]
     */
    public static function calculatePrice(float $damage, float $crit, float $health, ItemRarityEnum $rarity): array
    {
        $totalStats = $damage + $crit + $health;
        $goldPrice = (int) round($totalStats * ItemConfig::GOLD_PER_STAT_POINT);

        $diamondPrice = match ($rarity) {
            ItemRarityEnum::Epic => (int) round($goldPrice * 0.1),
            ItemRarityEnum::Legendary => (int) round($goldPrice * 0.17),
            default => 0,
        };

        return [$goldPrice, $diamondPrice];
    }


    /**
     * Roll bonus stats for an item based on its definition.
     *
     * The bonus stats are calculated as a random percentage (-20% to +20%) of the base stats defined in the item definition.
     *
     * @return array{int, int, int} [bonusDamage, bonusCrit, bonusHealth]
     */
    public static function rollBonusStats(ItemDefinition $definition): array
    {
        $bonusDamage = 0;
        $bonusCrit = 0;
        $bonusHealth = 0;

        if ($definition->getBaseDamage() > 0) {
            $randPercent = (mt_rand(-ItemConfig::BONUS_STAT_VARIANCE_PERCENT, ItemConfig::BONUS_STAT_VARIANCE_PERCENT) / 100);
            $bonusDamage = (int) round($definition->getBaseDamage() * $randPercent);
        }

        if ($definition->getBaseCrit() > 0) {
            $randPercent = (mt_rand(-ItemConfig::BONUS_STAT_VARIANCE_PERCENT, ItemConfig::BONUS_STAT_VARIANCE_PERCENT) / 100);
            $bonusCrit = (int) round($definition->getBaseCrit() * $randPercent);
        }

        if ($definition->getBaseHealth() > 0) {
            $randPercent = (mt_rand(-ItemConfig::BONUS_STAT_VARIANCE_PERCENT, ItemConfig::BONUS_STAT_VARIANCE_PERCENT) / 100);
            $bonusHealth = (int) round($definition->getBaseHealth() * $randPercent);
        }

        return [$bonusDamage, $bonusCrit, $bonusHealth];
    }
}
