<?php

declare(strict_types=1);

namespace App\Item\Definition;

enum ItemRarityEnum: string
{
    case Common = 'common';
    case Rare = 'rare';
    case Epic = 'epic';
    case Legendary = 'legendary';
}
