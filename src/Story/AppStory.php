<?php

namespace App\Story;

use App\Entity\Item\ItemRarityEnum;
use App\Entity\Item\ItemSlotEnum;
use App\Factory\ItemDefinitionFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    /**
     * Number of item variants per (slot × rarity × level) combination.
     */
    private const int VARIANTS_PER_COMBO = 5;

    /**
     * Level range for generated items.
     */
    private const int MIN_LEVEL = 1;
    private const int MAX_LEVEL = 20;

    public function build(): void
    {
        $this->generateItemDefinitions();
    }

    private function generateItemDefinitions(): void
    {
        $total = 0;

        foreach (ItemSlotEnum::cases() as $slot) {
            foreach (ItemRarityEnum::cases() as $rarity) {
                for ($level = self::MIN_LEVEL; $level <= self::MAX_LEVEL; $level++) {
                    for ($i = 0; $i < self::VARIANTS_PER_COMBO; $i++) {
                        ItemDefinitionFactory::new()
                            ->with([
                                'desiredSlot' => $slot,
                                'rarity' => $rarity,
                                'requiredLevel' => $level,
                            ])
                            ->create();
                        $total++;
                    }
                }
            }
        }

        $this->addNote(sprintf(
            'Vygenerováno %d ItemDefinitions (%d slotů × %d rarit × %d levelů × %d variant)',
            $total,
            count(ItemSlotEnum::cases()),
            count(ItemRarityEnum::cases()),
            self::MAX_LEVEL - self::MIN_LEVEL + 1,
            self::VARIANTS_PER_COMBO,
        ));
    }
}
