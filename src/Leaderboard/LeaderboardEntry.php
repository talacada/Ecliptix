<?php

declare(strict_types=1);

namespace App\Leaderboard;

use App\Character\Character\Character;
use Symfony\Component\Serializer\Attribute\Groups;

class LeaderboardEntry
{
    public const string LEADERBOARD_READ = 'leaderboard:read';

    #[Groups([self::LEADERBOARD_READ])]
    public int $rank;

    #[Groups([self::LEADERBOARD_READ])]
    public Character $character;

    public function __construct(int $rank, Character $character)
    {
        $this->rank = $rank;
        $this->character = $character;
    }
}
