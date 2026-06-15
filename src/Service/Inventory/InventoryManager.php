<?php

namespace App\Service\Inventory;

use App\Entity\Character\Character;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\Item;
use Doctrine\ORM\EntityManagerInterface;

class InventoryManager
{

    public function __construct(
        private EntityManagerInterface $entityManager
    ){ }

    //WITHOUT elixir logic NOW
    public function addToBackpack(Character $character, Item $item): CharacterInventory
    {
        //TODO add to backpack
        //todo check if has space (to be sure)
    }
}
