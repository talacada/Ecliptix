<?php

declare(strict_types=1);

namespace App\Item\Stats;

final class ElixirStatsConfig
{
    public const array VARIANTS = [
        ['bonus' => 10, 'days' => 3, 'name' => 'Small'],
        ['bonus' => 20, 'days' => 3, 'name' => 'Big'],
        ['bonus' => 25, 'days' => 6, 'name' => 'Large'],
    ];

    public const int COST_FACTOR = 50;
}
