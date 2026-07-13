<?php

namespace App\ApiResource\Leaderboard;

use App\Entity\Character\Character;
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
