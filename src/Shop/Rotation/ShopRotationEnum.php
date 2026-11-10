<?php

declare(strict_types=1);

namespace App\Shop\Rotation;

enum ShopRotationEnum: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Event = 'event';
}
