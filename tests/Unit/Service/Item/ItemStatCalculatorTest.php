<?php

namespace App\Tests\Unit\Service\Item;

use App\Entity\Item\ItemRarityEnum;
use App\Entity\Item\ItemSlotEnum;
use App\Service\Item\ItemStatCalculator;
use PHPUnit\Framework\TestCase;

class ItemStatCalculatorTest extends TestCase
{
    //TODO this is bad
    public function testCalculateStatsCommonWeaponLevelOne() {
        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Common, 1);

        $this->assertSame([8.0, 1.0, 0.0], $stats);
    }

    public function testCalculateStatsRandomWeapon() {
        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Common, 5);
        $this->assertSame([20.0, 5.0, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Common, 25);
        $this->assertSame([80.0, 25.0, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Rare, 1);
        $this->assertSame([20.0, 2.5, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Rare, 5);
        $this->assertSame([50.0, 12.5, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Rare, 25);
        $this->assertSame([200.0, 62.5, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Epic, 1);
        $this->assertSame([40.0, 5.0, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Epic, 5);
        $this->assertSame([100.0, 25.0, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Epic, 25);
        $this->assertSame([400.0, 125.0, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Legendary, 1);
        $this->assertSame([80.0, 10.0, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Legendary, 5);
        $this->assertSame([200.0, 50.0, 0.0], $stats);

        $stats = ItemStatCalculator::calculateStats(ItemSlotEnum::Weapon, ItemRarityEnum::Legendary, 25);
        $this->assertSame([800.0, 250.0, 0.0], $stats);
    }

}
