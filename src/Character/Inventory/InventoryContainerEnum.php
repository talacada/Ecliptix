<?php

declare(strict_types=1);

namespace App\Character\Inventory;

enum InventoryContainerEnum: string
{
    case Backpack = 'backpack';
    case Equipped = 'equipped';
    // case Marketplace = "Marketplace";
}
