<?php

namespace App\Service\Inventory;

use App\Entity\Character\Character;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\InventoryContainerEnum;
use App\Entity\Item\Item;
use App\Entity\Item\ItemSlotEnum;
use App\Repository\Character\CharacterInventoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class InventoryManager
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CharacterInventoryRepository $characterInventoryRepository,
    ){ }

    //WITHOUT elixir logic NOW

    /**
     * @throws Exception
     */
    public function addToBackpack(Character $character, Item $item): CharacterInventory
    {
        if ($character->getBackpackCapacity() <= count($this->characterInventoryRepository->getUnequippedItems($character))) {
            throw new Exception("Not enough backpack space");
        }

        $characterInventory = new CharacterInventory();
        $characterInventory->setCharacter($character);
        $characterInventory->setItem($item);
        $characterInventory->setPosition($this->getFirstAvailablePosition($character));
        $characterInventory->setContainer(InventoryContainerEnum::Backpack);

        return $characterInventory;
    }

    /**
     * @throws Exception
     */
    public function getFirstAvailablePosition(Character $character): int
    {
        $capacity = $character->getBackpackCapacity();
        $allTakenPositions = $this->characterInventoryRepository->getAllTakenPositions($character);
        $firstAvailablePosition = 0;

        for ($i = 1; $i <= $capacity; $i++) {
            if (!in_array($i, $allTakenPositions)) {
                $firstAvailablePosition = $i;
                break;
            }
        }

        if ($firstAvailablePosition === 0) {
            throw new Exception("Not enough backpack space");
        }

        return $firstAvailablePosition;
    }

    public function getEquippedItemBySlot(Character $character, ItemSlotEnum $slot): ?CharacterInventory
    {
        $allEquippedItems = $this->characterInventoryRepository->getEquippedItems($character);

        return array_find($allEquippedItems, fn($equippedItem) => $slot === $equippedItem->getItem()->getDefinition()->getDesiredSlot());

    }
}
