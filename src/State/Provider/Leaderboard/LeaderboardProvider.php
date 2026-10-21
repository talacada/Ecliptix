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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use function assert;

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
        assert(null !== $request);

        $searchedName = $request->query->get('name');
        $searchedRank = $request->query->getInt('rank');

        if (null !== $searchedName) {
            $searchedCharacter = $this->characterRepository->getCharacterByUserName($searchedName);

            if (null === $searchedCharacter) {
                throw new NotFoundHttpException("Character with name {$searchedName} not found");
            }
            $characterRank = $this->leaderboardRepository->findRankOfCharacter($searchedCharacter);
        } elseif (0 !== $searchedRank) {
            if ($this->leaderboardRepository->getLastRank() < $searchedRank) {
                throw new NotFoundHttpException("Rank {$searchedRank} not found");
            }
            $characterRank = $searchedRank;
        } else {
            $characterRank = $this->leaderboardRepository->findRankOfCharacter($character);
        }

        $totalItems = $this->leaderboardRepository->getLastRank();
        $half = (int) floor(self::PAGE_LIMIT / 2);
        $maxOffset = max(0, $totalItems - self::PAGE_LIMIT);
        $offset = max(0, min($maxOffset, $characterRank - $half - 1));

        $selectedCharacters = $this->leaderboardRepository->getLeaderboardSlice($offset, self::PAGE_LIMIT);

        $entries = [];
        $startRank = $offset + 1;
        $endRank = $startRank + count($selectedCharacters) - 1;

        foreach ($selectedCharacters as $index => $selectedCharacter) {
            $entries[] = new LeaderboardEntry($startRank + $index, $selectedCharacter);
        }

        return new LeaderboardResponse($startRank, $endRank, $totalItems, $entries);
    }
}
