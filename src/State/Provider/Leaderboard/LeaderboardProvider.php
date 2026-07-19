<?php

namespace App\State\Provider\Leaderboard;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Leaderboard\LeaderboardResponse;
use App\Repository\Character\CharacterRepository;
use App\Repository\Leaderboard\LeaderboardRepository;
use App\Security\LoggedInCharacter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class LeaderboardProvider implements ProviderInterface
{

    const int PAGE_LIMIT = 51;

    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterRepository $characterRepository,
        private LeaderboardRepository $leaderboardRepository,
        private RequestStack $requestStack,
    ) { }
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): LeaderboardResponse
    {
        $character = $this->loggedInCharacter->getCharacter();

        $request = $this->requestStack->getCurrentRequest();

        $searchedName = $request->query->get('name');
        $searchedRank = $request->query->getInt('rank');
        $onPage = $request->query->getInt('page');

        if ($searchedName !== null) {
            $searchedCharacter = $this->characterRepository->getCharacterByUserName($searchedName);

            if ($searchedCharacter === null) {
                throw new ResourceNotFoundException("Character with name {$searchedName} not found");
            }
            $characterRank = $this->leaderboardRepository->findRankOfCharacter($searchedCharacter);
            $selectedCharacters = $this->leaderboardRepository->getLeaderboardAroundRank($characterRank, self::PAGE_LIMIT);
            $entris = [];

            foreach ($selectedCharacters as $selectedCharacter) {
                $entris[] = $selectedCharacter;
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
