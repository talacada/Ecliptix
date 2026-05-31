<?php

namespace App\Entity\Item;

enum ItemRarityEnum: string {
    case Common = 'common';
    case Rare = 'rare';
    case Epic = 'epic';
    case Legendary = 'legendary';
}
