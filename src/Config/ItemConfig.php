<?php

namespace App\Config;

final class ItemConfig
{
    /**
     * Base value added before scaling — ensures even level 1 items have nonzero stats.
     */
    public const array STAT_BASE = [
        'weapon' => ['damage' => 5, 'crit' => 0, 'health' => 0],
        'helmet' => ['damage' => 0, 'crit' => 0, 'health' => 5],
        'armour' => ['damage' => 0, 'crit' => 0, 'health' => 8],
        'boots' => ['damage' => 0, 'crit' => 1, 'health' => 3],
        'ring_left' => ['damage' => 1, 'crit' => 1, 'health' => 0],
        'ring_right' => ['damage' => 1, 'crit' => 1, 'health' => 0],
        'necklace' => ['damage' => 0, 'crit' => 2, 'health' => 0],
        'elixir' => ['damage' => 0, 'crit' => 0, 'health' => 0],
    ];

    /**
     * How much each stat scales per requiredLevel for each slot type.
     */
    public const array STAT_SCALING = [
        'weapon' => ['damage' => 3, 'crit' => 1, 'health' => 0],
        'helmet' => ['damage' => 0, 'crit' => 1, 'health' => 4],
        'armour' => ['damage' => 0, 'crit' => 0, 'health' => 6],
        'boots' => ['damage' => 0, 'crit' => 2, 'health' => 2],
        'ring_left' => ['damage' => 1, 'crit' => 2, 'health' => 1],
        'ring_right' => ['damage' => 1, 'crit' => 2, 'health' => 1],
        'necklace' => ['damage' => 0, 'crit' => 3, 'health' => 2],
        'elixir' => ['damage' => 0, 'crit' => 0, 'health' => 0],
    ];

    /**
     * Rarity multiplier applied to all stats and prices.
     */
    public const array RARITY_MULTIPLIER = [
        'common' => 1.0,
        'rare' => 2.5,
        'epic' => 5.0,
        'legendary' => 10.0,
    ];

    /** Gold price = totalStats × this multiplier. Diamond price ≈ 1% of gold price. */
    public const int GOLD_PER_STAT_POINT = 10;
}
