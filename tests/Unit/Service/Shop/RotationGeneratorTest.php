<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Shop;

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
    // TODO: testGenerateCalculatesPricesAndBonusStatsCorrectly() - assert gold/diamond prices and bonus stats match item definition & level calculations
    // TODO: testGenerateSkipsOfferWhenRepositoryReturnsNull() - assert continues without error if findRandomElixir or findRandomByLevel returns null
}
