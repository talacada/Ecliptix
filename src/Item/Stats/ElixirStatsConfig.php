<?php

declare(strict_types=1);

namespace App\Item\Stats;

final class ElixirStatsConfig
{
    public const array VARIANTS = [
        ['bonus' => 10, 'days' => 3],
        ['bonus' => 20, 'days' => 3],
        ['bonus' => 25, 'days' => 6],
    ];

    public const int COST_FACTOR = 50;
}
