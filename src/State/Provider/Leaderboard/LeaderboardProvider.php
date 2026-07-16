<?php

namespace App\State\Provider\Leaderboard;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Leaderboard\LeaderboardResponse;
use App\Repository\Character\CharacterRepository;
use App\Repository\Leaderboard\LeaderboardRepository;
use App\Security\LoggedInCharacter;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class LeaderboardProvider implements ProviderInterface
{

    const int PAGE_LIMIT = 50;

    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterRepository $characterRepository,
        private LeaderboardRepository $leaderboardRepository,
    ) { }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): LeaderboardResponse
    {
        $character = $this->loggedInCharacter->getCharacter();

        $searchedName = null;
        $searchedRank = null;
        $onPage = null;

        if (isset($uriVariables['page'])) {
            $onPage = (int)$uriVariables['page'];
        }
        if (isset($uriVariables['name'])) {
            $searchedName = (string)$uriVariables['name'];
        }
        if (isset($uriVariables['rank'])) {
            $searchedRank = (int)$uriVariables['rank'];
        }

        if ($searchedName !== null) {
            $searchedCharacter = $this->characterRepository->getCharacterByName($searchedName);

            if ($searchedCharacter !== null) {
                $this->leaderboardRepository->findRankOfCharacter($searchedCharacter);
            }else {
                throw new ResourceNotFoundException("Character with name {$searchedName} not found");
            }
        }elseif ($searchedRank !== null) {
            //todo search by rank
        }elseif ($onPage !== null) {
            //todo get page
        }else {
            //todo get own character rank and surrounding characters
        }


    }
}
