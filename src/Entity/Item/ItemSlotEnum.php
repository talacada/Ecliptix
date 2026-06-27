<?php

namespace App\Entity\Item;

enum ItemSlotEnum: string {
    case Elixir    = 'elixir';
    case Weapon    = 'weapon';
    case Helmet    = 'helmet';
    case Armour    = 'armour';
    case Boots     = 'boots';
    case RingLeft  = 'ring_left';
    case RingRight = 'ring_right';
    case Necklace  = 'necklace';
}
