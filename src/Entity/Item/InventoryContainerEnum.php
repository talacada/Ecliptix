<?php

declare(strict_types=1);

namespace App\Entity\Item;

enum InventoryContainerEnum: string
{
    case Backpack = 'backpack';
    case Equipped = 'equipped';
    // case Marketplace = "Marketplace";
}
