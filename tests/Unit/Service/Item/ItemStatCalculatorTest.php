<?php

namespace App\Tests\Unit\Service\Item;

use App\Config\ItemConfig;
use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemRarityEnum;
use App\Entity\Item\ItemSlotEnum;
use App\Service\Item\ItemStatCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ItemStatCalculatorTest extends TestCase
{
    /**
     * @param array{float, float, float} $expectedStats
     */
    #[DataProvider('provideStatsScenarios')]
    public function testCalculateStats(
        ItemSlotEnum $slot,
        ItemRarityEnum $rarity,
        int $level,
        array $expectedStats
    ): void {
        $stats = ItemStatCalculator::calculateStats($slot, $rarity, $level);

        $this->assertSame($expectedStats, $stats);
    }

    /**
     * @param array{int, int} $expectedPrice
     */
    #[DataProvider('providePriceScenarios')]
    public function testCalculatePrice(
        float $damage,
        float $crit,
        float $health,
        ItemRarityEnum $rarity,
        array $expectedPrice,
    ): void {
        $stats = ItemStatCalculator::calculatePrice($damage, $crit, $health, $rarity);

        $this->assertSame($expectedPrice, $stats);
    }

    public function testRollBonusStatsReturnsZeroWhenBaseStatsAreZero(): void
    {
        $definition = new ItemDefinition();
        $definition->setBaseDamage(0);
        $definition->setBaseCrit(0);
        $definition->setBaseHealth(0);

        [$bonusDamage, $bonusCrit, $bonusHealth] = ItemStatCalculator::rollBonusStats($definition);

        $this->assertSame(0, $bonusDamage);
        $this->assertSame(0, $bonusCrit);
        $this->assertSame(0, $bonusHealth);
    }

    public function testRollBonusStatsStaysWithinExpectedRange(): void
    {
        $definition = new ItemDefinition();
        $definition->setBaseDamage(100);
        $definition->setBaseCrit(50);
        $definition->setBaseHealth(200);

        $variance = ItemConfig::BONUS_STAT_VARIANCE_PERCENT / 100;
        $expectedMaxDamage = (int) round(100 * $variance);
        $expectedMaxCrit = (int) round(50 * $variance);
        $expectedMaxHealth = (int) round(200 * $variance);

        for ($i = 0; $i < 10; ++$i) {
            [$bonusDamage, $bonusCrit, $bonusHealth] = ItemStatCalculator::rollBonusStats($definition);

            $this->assertGreaterThanOrEqual(-$expectedMaxDamage, $bonusDamage);
            $this->assertLessThanOrEqual($expectedMaxDamage, $bonusDamage);

            $this->assertGreaterThanOrEqual(-$expectedMaxCrit, $bonusCrit);
            $this->assertLessThanOrEqual($expectedMaxCrit, $bonusCrit);

            $this->assertGreaterThanOrEqual(-$expectedMaxHealth, $bonusHealth);
            $this->assertLessThanOrEqual($expectedMaxHealth, $bonusHealth);
        }
    }

    /**
     * @return iterable<string, array{ItemSlotEnum, ItemRarityEnum, int, array{float, float, float}}>
     */
    public static function provideStatsScenarios(): iterable
    {
        yield 'weapon common lvl 1' => [
            ItemSlotEnum::Weapon,
            ItemRarityEnum::Common,
            1,
            [8.0, 1.0, 0.0],
        ];

        yield 'weapon epic lvl 5' => [
            ItemSlotEnum::Weapon,
            ItemRarityEnum::Epic,
            5,
            [100.0, 25.0, 0.0],
        ];

        yield 'armour rare lvl 10' => [
            ItemSlotEnum::Armour,
            ItemRarityEnum::Rare,
            10,
            [0.0, 0.0, 170.0],
        ];

        yield 'elixir has zero stats at any level' => [
            ItemSlotEnum::Elixir,
            ItemRarityEnum::Legendary,
            50,
            [0.0, 0.0, 0.0],
        ];

        yield 'helmet has no damage' => [
            ItemSlotEnum::Helmet,
            ItemRarityEnum::Legendary,
            25,
            [0.0, 250.0, 1050.0],
        ];

        yield 'armour has no dmg no crit' => [
            ItemSlotEnum::Armour,
            ItemRarityEnum::Epic,
            50,
            [0.0, 0.0, 1540.0],
        ];

        yield 'boots has no dmg' => [
            ItemSlotEnum::Boots,
            ItemRarityEnum::Common,
            111,
            [0.0, 223.0, 225.0],
        ];
    }

    /**
     * @return iterable<string, array{float, float, float, ItemRarityEnum, array{int, int}}>
     */
    public static function providePriceScenarios(): iterable
    {
        yield 'Common price' => [
            10.0,
            8.0,
            20.0,
            ItemRarityEnum::Common,
            [380, 0],
        ];

        yield 'Rare price' => [
            10.0,
            8.0,
            20.0,
            ItemRarityEnum::Rare,
            [380, 0],
        ];

        yield 'Epic price' => [
            10.0,
            8.0,
            20.0,
            ItemRarityEnum::Epic,
            [380, 38],
        ];

        yield 'Legendary price' => [
            10.0,
            8.0,
            20.0,
            ItemRarityEnum::Legendary,
            [380, 65],
        ];
    }

}
