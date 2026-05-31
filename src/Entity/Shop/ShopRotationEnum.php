<?php

namespace App\Entity\Shop;

enum ShopRotationEnum: string
{
    case Daily  = 'daily';
    case Weekly = 'weekly';
    case Event  = 'event';
}
