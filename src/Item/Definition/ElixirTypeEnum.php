<?php

declare(strict_types=1);

namespace App\Item\Definition;

enum ElixirTypeEnum: string
{
    case Damage = 'damage';
    case Health = 'health';
    case Critical = 'critical';
}
