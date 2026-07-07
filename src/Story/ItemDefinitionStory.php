<?php

declare(strict_types=1);

namespace App\Story;

use App\Entity\Item\ElixirTypeEnum;
use App\Entity\Item\ItemRarityEnum;
use App\Entity\Item\ItemSlotEnum;
use App\Factory\ElixirDefinitionFactory;
use App\Factory\ItemDefinitionFactory;

final class ItemDefinitionStory
{
    /**
     * Number of item variants per (slot × rarity × level) combination.
     */
    private const int VARIANTS_PER_COMBO = 5;

    /**
     * Level range for generated items.
     */
    private const int MIN_LEVEL = 1;
    private const int MAX_LEVEL = 5;

    public function generate(): void
    {
        foreach (ItemSlotEnum::cases() as $slot) {
            if (ItemSlotEnum::Elixir !== $slot) {
                foreach (ItemRarityEnum::cases() as $rarity) {
                    for ($level = self::MIN_LEVEL; $level <= self::MAX_LEVEL; ++$level) {
                        for ($i = 0; $i < self::VARIANTS_PER_COMBO; ++$i) {
                            ItemDefinitionFactory::new()
                                ->with([
                                    'desiredSlot' => $slot,
                                    'rarity' => $rarity,
                                    'requiredLevel' => $level,
                                ])
                                ->create();
                        }
                    }
                }
            } else {
                foreach (ElixirTypeEnum::cases() as $elixirType) {
                    foreach (ElixirDefinitionFactory::VARIANTS as $variant) {
                        ElixirDefinitionFactory::new()
                            // With is rewriting default values in factory...
                            ->with([
                                'elixirType' => $elixirType,
                                'percentageBonus' => $variant['bonus'],
                                'durationSeconds' => $variant['days'] * 86400,
                            ])
                            ->create();
                    }
                }
            }
        }
    }
}
