<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemRarityEnum;
use App\Entity\Item\ItemSlotEnum;
use App\Service\Item\ItemStatCalculator;
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

    protected function defaults(): array
    {
        $equipmentSlots = array_filter(
            ItemSlotEnum::cases(),
            fn (ItemSlotEnum $singleSlotEnum) => ItemSlotEnum::Elixir !== $singleSlotEnum,
        );
        $slot = self::faker()->randomElement($equipmentSlots);
        $rarity = self::faker()->randomElement(ItemRarityEnum::cases());
        $level = self::faker()->numberBetween(1, 20);

        assert($slot instanceof ItemSlotEnum);
        assert($rarity instanceof ItemRarityEnum);

        [$damage, $crit, $health] = ItemStatCalculator::calculateStats($slot, $rarity, $level);
        [$goldPrice, $diamondPrice] = ItemStatCalculator::calculatePrice($damage, $crit, $health, $rarity);

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


}
