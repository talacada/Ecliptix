<?php

declare(strict_types=1);

namespace App\Entity\Shop;

enum ShopRotationEnum: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Event = 'event';
}
