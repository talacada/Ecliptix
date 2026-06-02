<?php

namespace App\Factory;

use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemRarityEnum;
use App\Entity\Item\ItemSlotEnum;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ItemDefinition>
 */
final class ItemDefinitionFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ItemDefinition::class;
    }

    /**
     * Rarity multiplier applied to all stats and prices.
     * Common=1.0, Rare=2.5, Epic=5.0, Legendary=10.0
     */
    private const array RARITY_MULTIPLIER = [
        'common'    => 1.0,
        'rare'      => 2.5,
        'epic'      => 5.0,
        'legendary' => 10.0,
    ];

    /**
     * How much each stat scales per requiredLevel for each slot type.
     * Think of this as "stat budget" per level — tune these to your game design.
     */
    private const array STAT_SCALING = [
        'weapon'     => ['damage' => 3, 'crit' => 1, 'health' => 0],
        'helmet'     => ['damage' => 0, 'crit' => 1, 'health' => 4],
        'armour'     => ['damage' => 0, 'crit' => 0, 'health' => 6],
        'boots'      => ['damage' => 0, 'crit' => 2, 'health' => 2],
        'elixir'     => ['damage' => 1, 'crit' => 1, 'health' => 3],
        'ring_left'  => ['damage' => 1, 'crit' => 2, 'health' => 1],
        'ring_right' => ['damage' => 1, 'crit' => 2, 'health' => 1],
        'necklace'   => ['damage' => 0, 'crit' => 3, 'health' => 2],
    ];

    /**
     * Base value added before scaling — ensures even level 1 items have nonzero stats.
     */
    private const array STAT_BASE = [
        'weapon'     => ['damage' => 5, 'crit' => 0, 'health' => 0],
        'helmet'     => ['damage' => 0, 'crit' => 0, 'health' => 5],
        'armour'     => ['damage' => 0, 'crit' => 0, 'health' => 8],
        'boots'      => ['damage' => 0, 'crit' => 1, 'health' => 3],
        'elixir'     => ['damage' => 0, 'crit' => 0, 'health' => 5],
        'ring_left'  => ['damage' => 1, 'crit' => 1, 'health' => 0],
        'ring_right' => ['damage' => 1, 'crit' => 1, 'health' => 0],
        'necklace'   => ['damage' => 0, 'crit' => 2, 'health' => 0],
    ];

    /** Gold price = totalStats × this multiplier. Diamond price ≈ 1% of gold price. */
    private const int GOLD_PER_STAT_POINT = 10;

    protected function defaults(): array
    {
        $slot = self::faker()->randomElement(ItemSlotEnum::cases());
        $rarity = self::faker()->randomElement(ItemRarityEnum::cases());
        $level = self::faker()->numberBetween(1, 20);

        [$damage, $crit, $health] = self::calculateStats($slot, $rarity, $level);
        [$goldPrice, $diamondPrice] = self::calculatePrice($damage, $crit, $health, $rarity);

        return [
            'name' => self::faker()->words(2, true),
            'desiredSlot' => $slot,
            'rarity' => $rarity,
            'requiredLevel' => $level,
            'baseDamage' => (int) round($damage),
            'baseCrit' => (int) round($crit),
            'baseHealth' => (int) round($health),
            'baseGoldPrice' => $goldPrice,
            'baseDiamondPrice' => $diamondPrice,
            'description' => self::faker()->optional()->sentence(),
        ];
    }

    /**
     * Compute stats for a given slot, rarity, and level.
     *
     * Formula: (base + scaling × level) × rarityMultiplier
     *
     * @return array{float, float, float} [damage, crit, health]
     */
    public static function calculateStats(ItemSlotEnum $slot, ItemRarityEnum $rarity, int $level): array
    {
        $key = $slot->value;
        $multiplier = self::RARITY_MULTIPLIER[$rarity->value];
        $scaling = self::STAT_SCALING[$key];
        $base = self::STAT_BASE[$key];

        $damage = ($base['damage'] + $scaling['damage'] * $level) * $multiplier;
        $crit = ($base['crit'] + $scaling['crit'] * $level) * $multiplier;
        $health = ($base['health'] + $scaling['health'] * $level) * $multiplier;

        return [$damage, $crit, $health];
    }

    /**
     * Compute prices from stats and rarity.
     *
     * Gold: sum of all stats × GOLD_PER_STAT_POINT — already includes rarity scaling
     *       since stats are scaled before this call.
     * Diamond: nonzero only for Epic+ rarity (1% of gold price).
     *
     * @return array{int, int} [goldPrice, diamondPrice]
     */
    public static function calculatePrice(float $damage, float $crit, float $health, ItemRarityEnum $rarity): array
    {
        $totalStats = $damage + $crit + $health;
        $goldPrice = (int) round($totalStats * self::GOLD_PER_STAT_POINT);

        $diamondPrice = match ($rarity) {
            ItemRarityEnum::Epic     => (int) round($goldPrice * 0.01),
            ItemRarityEnum::Legendary => (int) round($goldPrice * 0.02),
            default                   => 0,
        };

        return [$goldPrice, $diamondPrice];
    }
}
