<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Shop;

use App\Config\ItemConfig;
use App\Entity\Character\Character;
use App\Entity\Item\ElixirDefinition;
use App\Entity\Item\Item;
use App\Entity\Item\ItemDefinition;
use App\Entity\Shop\ShopRotation;
use App\Entity\Shop\ShopRotationEnum;
use App\Repository\Item\ItemDefinitionRepository;
use App\Repository\Shop\ShopRotationRepository;
use App\Service\Shop\RotationGenerator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RotationGeneratorTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private ShopRotationRepository&MockObject $shopRotationRepository;
    private ItemDefinitionRepository&MockObject $itemDefinitionRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->shopRotationRepository = $this->createMock(ShopRotationRepository::class);
        $this->itemDefinitionRepository = $this->createMock(ItemDefinitionRepository::class);
    }
    public function testGenerateDailyRotationCleansUpOldExpiredRotations(): void
    {
        $character = new Character();

        $this->shopRotationRepository
            ->expects($this->once())
            ->method('findAllExpired')
            ->with($character)
            ->willReturn([new ShopRotation(), new ShopRotation(), new ShopRotation()]);

        $this->entityManager
            ->expects($this->exactly(3))
            ->method('remove');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->itemDefinitionRepository
            ->expects($this->exactly(2))
            ->method('findRandomElixir');

        $generator = new RotationGenerator($this->itemDefinitionRepository, $this->entityManager, $this->shopRotationRepository);
        $generator->generateDaily($character);
    }

    public function testGenerateCreatesDailyRotationWithCorrectDateRange(): void
    {
        $character = new Character();

        $this->shopRotationRepository
            ->expects($this->once())
            ->method('findAllExpired')
            ->with($character)
            ->willReturn([]);

        $this->entityManager
            ->expects($this->never())
            ->method('remove');

        $this->itemDefinitionRepository
            ->expects($this->exactly(2))
            ->method('findRandomElixir');

        $generator = new RotationGenerator($this->itemDefinitionRepository, $this->entityManager, $this->shopRotationRepository);
        $rotation = $generator->generateDaily($character);

        $this->assertSame(ShopRotationEnum::Daily, $rotation->getRotationType());
        $this->assertEquals(new DateTimeImmutable('midnight'), $rotation->getValidFrom());
        $this->assertEquals(new DateTimeImmutable('tomorrow'), $rotation->getValidUntil());
        $this->assertSame($character, $rotation->getCharacter());

    }

    public function testGenerateCreatesExactQuotaOfOffers(): void
    {
        $character = new Character();

        $this->shopRotationRepository
            ->expects($this->once())
            ->method('findAllExpired')
            ->with($character)
            ->willReturn([]);

        $this->itemDefinitionRepository
            ->expects($this->exactly(2))
            ->method('findRandomElixir')
            ->willReturn(new ElixirDefinition());

        $this->itemDefinitionRepository
            ->expects($this->exactly(8))
            ->method('findRandomByLevel')
            ->with($character->getLevel())
            ->willReturn(new ItemDefinition());

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $generator = new RotationGenerator($this->itemDefinitionRepository, $this->entityManager, $this->shopRotationRepository);
        $rotation = $generator->generateDaily($character);

        $elixirCount = 0;
        $equipmentCount = 0;

        $this->assertCount(10, $rotation->getShopOffers());

        foreach ($rotation->getShopOffers() as $offer) {
            $this->assertSame($rotation, $offer->getRotation());

            if ($offer->getItemDefinition() instanceof ElixirDefinition) {
                $elixirCount++;
            } elseif ($offer->getItemDefinition() instanceof ItemDefinition) {
                $equipmentCount++;
            }
        }

        $this->assertSame(2, $elixirCount);
        $this->assertSame(8, $equipmentCount);

    }

    #[DataProvider('providePriceAndStats')]
    public function testGenerateCalculatesPricesAndBonusStatsCorrectly(
        int $characterLevel,
        int $baseGoldPrice,
        int $baseDiamondPrice,
        int $baseDamage,
        int $baseCrit,
        int $baseHealth,
    ): void
    {
        $character = new Character();
        $character->setLevel($characterLevel);

        $definition = new ItemDefinition();
        $definition->setBaseGoldPrice($baseGoldPrice);
        $definition->setBaseDiamondPrice($baseDiamondPrice);
        $definition->setBaseDamage($baseDamage);
        $definition->setBaseCrit($baseCrit);
        $definition->setBaseHealth($baseHealth);

        $elixirDef = new ElixirDefinition();
        $elixirDef->setBaseGoldPrice($baseGoldPrice);
        $elixirDef->setBaseDiamondPrice($baseDiamondPrice);

        $this->shopRotationRepository
            ->expects($this->once())
            ->method('findAllExpired')
            ->with($character)
            ->willReturn([]);

        $this->itemDefinitionRepository
            ->expects($this->exactly(2))
            ->method('findRandomElixir')
            ->willReturn($elixirDef);

        $this->itemDefinitionRepository
            ->expects($this->exactly(8))
            ->method('findRandomByLevel')
            ->with($character->getLevel())
            ->willReturn($definition);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $goldPriceMinimum = (int) ($baseGoldPrice * $characterLevel * ItemConfig::SHOP_PRICE_VARIANCE_MIN / 100);
        $goldPriceMaximum = (int) ($baseGoldPrice * $characterLevel * ItemConfig::SHOP_PRICE_VARIANCE_MAX / 100);

        $diamondPriceMinimum = (int) ($baseDiamondPrice * ItemConfig::SHOP_PRICE_VARIANCE_MIN / 100);
        $diamondPriceMaximum = (int) ($baseDiamondPrice * ItemConfig::SHOP_PRICE_VARIANCE_MAX / 100);

        $damageMinimum = (int) round($baseDamage * -ItemConfig::BONUS_STAT_VARIANCE_PERCENT / 100);
        $damageMaximum = (int) round($baseDamage * ItemConfig::BONUS_STAT_VARIANCE_PERCENT / 100);

        $critMinimum = (int) round($baseCrit * -ItemConfig::BONUS_STAT_VARIANCE_PERCENT / 100);
        $critMaximum = (int) round($baseCrit * ItemConfig::BONUS_STAT_VARIANCE_PERCENT / 100);

        $healthMinimum = (int) round($baseHealth * -ItemConfig::BONUS_STAT_VARIANCE_PERCENT / 100);
        $healthMaximum = (int) round($baseHealth * ItemConfig::BONUS_STAT_VARIANCE_PERCENT / 100);

        $generator = new RotationGenerator($this->itemDefinitionRepository, $this->entityManager, $this->shopRotationRepository);
        $rotation = $generator->generateDaily($character);

        foreach ($rotation->getShopOffers() as $offer) {
            $this->assertGreaterThanOrEqual($goldPriceMinimum, $offer->getGoldPrice());
            $this->assertLessThanOrEqual($goldPriceMaximum, $offer->getGoldPrice());

            if ($offer->getItemDefinition() instanceof ElixirDefinition) {
                $this->assertSame($baseDiamondPrice, $offer->getDiamondPrice());
                $this->assertSame(0, $offer->getBonusDamage());
                $this->assertSame(0, $offer->getBonusCrit());
                $this->assertSame(0, $offer->getBonusHealth());
            }else {
                $this->assertGreaterThanOrEqual($diamondPriceMinimum, $offer->getDiamondPrice());
                $this->assertLessThanOrEqual($diamondPriceMaximum, $offer->getDiamondPrice());

                $this->assertGreaterThanOrEqual($damageMinimum, $offer->getBonusDamage());
                $this->assertLessThanOrEqual($damageMaximum, $offer->getBonusDamage());

                $this->assertGreaterThanOrEqual($critMinimum, $offer->getBonusCrit());
                $this->assertLessThanOrEqual($critMaximum, $offer->getBonusCrit());

                $this->assertGreaterThanOrEqual($healthMinimum, $offer->getBonusHealth());
                $this->assertLessThanOrEqual($healthMaximum, $offer->getBonusHealth());
            }
        }
    }

    public function testGenerateSkipsOfferWhenRepositoryReturnsNull(): void
    {
        $character = new Character();

        $this->shopRotationRepository
            ->expects($this->once())
            ->method('findAllExpired')
            ->with($character)
            ->willReturn([]);

        $this->itemDefinitionRepository
            ->expects($this->exactly(2))
            ->method('findRandomElixir')
            ->willReturn(null);

        $this->itemDefinitionRepository
            ->expects($this->exactly(8))
            ->method('findRandomByLevel')
            ->with($character->getLevel())
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $generator = new RotationGenerator($this->itemDefinitionRepository, $this->entityManager, $this->shopRotationRepository);
        $rotation = $generator->generateDaily($character);

        $this->assertEmpty($rotation->getShopOffers());
    }
    public static function providePriceAndStats(): iterable
    {
        yield 'level 1 basic item' => [
            1,
            100,
            50,
            20,
            10,
            50,
        ];

        yield 'level 10 advanced item' => [
            10,
            250,
            100,
            80,
            30,
            200,
        ];
    }
}
