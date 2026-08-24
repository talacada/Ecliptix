<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Inventory;

use App\Entity\Character\Character;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\ElixirDefinition;
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
        $characterInventory->setQuantity(3);

        $this->characterInventoryRepository
            ->expects($this->once())
            ->method('getByDefinition')
            ->with($character, 0)
            ->willReturn($characterInventory);

        $result = $this->manager->addToBackpack($character, $item);

        $this->assertSame(4, $characterInventory->getQuantity());
    }
}
