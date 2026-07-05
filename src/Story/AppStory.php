<?php

namespace App\Story;

use App\Entity\Item\ElixirTypeEnum;
use App\Entity\Item\ItemRarityEnum;
use App\Entity\Item\ItemSlotEnum;
use App\Factory\ElixirDefinitionFactory;
use App\Factory\ItemDefinitionFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;
#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function __construct(
        private ItemDefinitionStory $itemDefinitionStory,
        private CharacterStory $characterStory,
    ) {}

    public function build(): void
    {
        $this->itemDefinitionStory->generate();
        $this->characterStory->generate();
    }
}

