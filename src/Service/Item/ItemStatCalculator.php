<?php

namespace App\Service\Item;

use App\Config\ItemConfig;
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
}
