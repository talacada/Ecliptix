<?php

namespace App\State\Provider\Leaderboard;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Leaderboard\LeaderboardResponse;
use App\Security\LoggedInCharacter;

class LeaderboardProvider implements ProviderInterface
{

    const int PAGE_LIMIT = 50;

    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
    ) { }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): LeaderboardResponse
    {
        $character = $this->loggedInCharacter->getCharacter();

        $searchedName = null;
        $searchedRank = null;
        $onPage = 1;


    }
}
