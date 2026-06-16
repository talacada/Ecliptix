<?php

namespace App\Service\Inventory;

use App\Entity\Character\Character;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\Item;
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
        if ($character->getBackpackCapacity() <= count($this->characterInventoryRepository->findAllUnequipped($character))) {
            throw new Exception("Not enough backpack space");
        }

        $characterInventory = new CharacterInventory();
        $characterInventory->setCharacter($character);
        $characterInventory->setItem($item);

        return $characterInventory;
    }
}
