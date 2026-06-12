<?php

namespace App\Service\Item;

use App\Entity\Item\Item;
use App\Entity\Item\ItemDefinition;

class ItemFactory
{

    static function createFromDefinition(ItemDefinition $definition): Item
    {
        $item = new Item();

        $item->setDefinition($definition);
    }
}
