<?php

declare(strict_types=1);

namespace App\Character\Inventory;

use App\Character\Character\Character;
use App\Item\Definition\ItemSlotEnum;
use App\Item\Item\Item;
use App\Item\Stats\ItemConfig;

class InventoryManager
{
    public function __construct(
        private CharacterInventoryRepository $characterInventoryRepository,
    ) {
    }

    public function addToBackpack(Character $character, Item $item, int $goldPaid, int $diamondPaid): CharacterInventory
    {
        $definition = $item->getDefinition();

        $willSellForGold = (int) ($goldPaid * ItemConfig::SELL_GOLD_PRICE_MULTIPLIER);
        $willSellForDiamond = (int) ($diamondPaid * ItemConfig::SELL_DIAMOND_PRICE_MULTIPLIER);

        if ($definition->isStackable()) {
            $existingElixirStack = $this->characterInventoryRepository->getByDefinition($character, $definition->getId() ?? 0);

            if (null !== $existingElixirStack) {
                $existingElixirStack->addQuantity(1);
                $existingElixirStack->addGoldPaid($willSellForGold);
                $existingElixirStack->addDiamondPaid($willSellForDiamond);

                return $existingElixirStack;
            }
        }

        if ($character->getBackpackCapacity() <= count($this->characterInventoryRepository->getUnequippedItems($character))) {
            throw new BackpackFullException();
        }

        $characterInventory = new CharacterInventory();
        $characterInventory->setCharacter($character);
        $characterInventory->setItem($item);
        $characterInventory->setPosition($this->getFirstAvailablePosition($character));
        $characterInventory->setContainer(InventoryContainerEnum::Backpack);
        $characterInventory->setGoldPaid($willSellForGold);
        $characterInventory->setDiamondPaid($willSellForDiamond);

        return $characterInventory;
    }

    public function getFirstAvailablePosition(Character $character): int
    {
        $capacity = $character->getBackpackCapacity();
        $allTakenPositions = $this->characterInventoryRepository->getAllTakenPositions($character);
        $firstAvailablePosition = 0;

        for ($i = 1; $i <= $capacity; ++$i) {
            if (!in_array($i, $allTakenPositions)) {
                $firstAvailablePosition = $i;
                break;
            }
        }

        if (0 === $firstAvailablePosition) {
            throw new BackpackFullException();
        }

        return $firstAvailablePosition;
    }

    public function getEquippedItemBySlot(Character $character, ItemSlotEnum $slot): ?CharacterInventory
    {
        $allEquippedItems = $this->characterInventoryRepository->getEquippedItems($character);

        return array_find($allEquippedItems, fn ($equippedItem) => $slot === $equippedItem->getItem()->getDefinition()->getDesiredSlot());
    }
}
