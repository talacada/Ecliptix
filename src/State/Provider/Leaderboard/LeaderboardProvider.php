<?php

declare(strict_types=1);

namespace App\State\Provider\Leaderboard;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Leaderboard\LeaderboardEntry;
use App\ApiResource\Leaderboard\LeaderboardResponse;
use App\Repository\Character\CharacterRepository;
use App\Repository\Leaderboard\LeaderboardRepository;
use App\Security\LoggedInCharacter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @implements ProviderInterface<LeaderboardResponse>
 */
class LeaderboardProvider implements ProviderInterface
{
    public const int PAGE_LIMIT = 51;

    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterRepository $characterRepository,
        private LeaderboardRepository $leaderboardRepository,
        private RequestStack $requestStack,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): LeaderboardResponse
    {
        $character = $this->loggedInCharacter->getCharacter();

        $request = $this->requestStack->getCurrentRequest();
        \assert(null !== $request);

        $searchedName = $request->query->get('name');
        $searchedRank = $request->query->getInt('rank');

        if (null !== $searchedName) {
            $searchedCharacter = $this->characterRepository->getCharacterByUserName($searchedName);

            if (null === $searchedCharacter) {
                throw new ResourceNotFoundException("Character with name {$searchedName} not found");
            }
            $characterRank = $this->leaderboardRepository->findRankOfCharacter($searchedCharacter);
        } elseif (0 !== $searchedRank) {
            if ($this->leaderboardRepository->getLastRank() < $searchedRank) {
                throw new ResourceNotFoundException("Rank {$searchedRank} not found");
            }
            $characterRank = $searchedRank;
        } else {
            $characterRank = $this->leaderboardRepository->findRankOfCharacter($character);
        }

        $selectedCharacters = $this->leaderboardRepository->getLeaderboardAroundRank($characterRank, self::PAGE_LIMIT);
        $totalItems = $this->leaderboardRepository->getLastRank();
        $entries = [];

        $half = (int) floor(self::PAGE_LIMIT / 2);
        $startRank = max(1, $characterRank - $half);
        $endRank = $startRank + count($selectedCharacters) - 1;

        foreach ($selectedCharacters as $index => $selectedCharacter) {
            $entries[] = new LeaderboardEntry($startRank + $index, $selectedCharacter);
        }

        return new LeaderboardResponse($startRank, $endRank, $totalItems, $entries);
    }
}
