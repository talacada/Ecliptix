<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Inventory;

use App\Entity\Character\Character;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\ElixirDefinition;
use App\Entity\Item\InventoryContainerEnum;
use App\Entity\Item\Item;
use App\Entity\Item\ItemDefinition;
use App\Repository\Character\CharacterInventoryRepository;
use App\Service\Inventory\InventoryManager;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InventoryManagerTest extends TestCase
{
    private CharacterInventoryRepository&MockObject $characterInventoryRepository;
    private InventoryManager $manager;
    protected function setUp(): void
    {
        $this->characterInventoryRepository = $this->createMock(CharacterInventoryRepository::class);

        $this->manager = new InventoryManager($this->characterInventoryRepository);
    }

    /**
     * @throws Exception
     */
    public function testIncreaseElixirStack(): void
    {
        $definition = new ElixirDefinition();

        $item = new Item();
        $item->setDefinition($definition);

        $character = new Character();

        $characterInventory = new CharacterInventory();
        $characterInventory->setCharacter($character);
        $characterInventory->setItem($item);
        $characterInventory->setQuantity(2);
        $characterInventory->setPosition(4);
        $characterInventory->setContainer(InventoryContainerEnum::Backpack);

        $this->characterInventoryRepository
            ->expects($this->once())
            ->method('getByDefinition')
            ->with($character, 0)
            ->willReturn($characterInventory);

        $result = $this->manager->addToBackpack($character, $item);

        $this->assertSame(InventoryContainerEnum::Backpack, $result->getContainer());
        $this->assertSame(3, $result->getQuantity());
        $this->assertSame(4, $result->getPosition());
    }

    /**
     * @throws Exception
     */
    public function testAddingButNoBackpackSpace(): void
    {
        $definition = new ItemDefinition();

        $item = new Item();
        $item->setDefinition($definition);

        $character = new Character();
        $character->setBackpackCapacity(4);

        $characterInventory = new CharacterInventory();
        $characterInventory->setCharacter($character);
        $characterInventory->setItem($item);
        $characterInventory->setQuantity(2);
        $characterInventory->setPosition(4);
        $characterInventory->setContainer(InventoryContainerEnum::Backpack);

        $this->characterInventoryRepository
            ->expects($this->once())
            ->method('getUnequippedItems')
            ->with($character)
            ->willReturn([$characterInventory, $characterInventory, $characterInventory, $characterInventory]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Not enough backpack space');

        $this->manager->addToBackpack($character, $item);
    }

    /**
     * @throws Exception
     */
    public function testSuccessfullyAddingItem(): void
    {
        $definition = new ItemDefinition();

        $item = new Item();
        $item->setDefinition($definition);

        $character = new Character();

        $characterInventory = new CharacterInventory();

        $this->characterInventoryRepository
            ->expects($this->once())
            ->method('getUnequippedItems')
            ->with($character)
            ->willReturn([$characterInventory, $characterInventory, $characterInventory]);

        $this->characterInventoryRepository
            ->expects($this->once())
            ->method('getAllTakenPositions')
            ->with($character)
            ->willReturn([0, 3, 2]);

        $result = $this->manager->addToBackpack($character, $item);

        $this->assertSame(InventoryContainerEnum::Backpack, $result->getContainer());
        $this->assertSame(1, $result->getPosition());
        $this->assertSame(1, $result->getQuantity());
        $this->assertSame($character, $result->getCharacter());
        $this->assertSame($item, $result->getItem());
    }

    //TODO continue with two more methods in InventoryManager
}
