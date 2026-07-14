<?php

namespace App\ApiResource\Leaderboard;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\QueryParameter;
use App\Entity\Character\Character;
use App\State\Provider\Leaderboard\LeaderboardProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/leaderboard',
            paginationEnabled: false,
            normalizationContext: ['groups' => [self::LEADERBOARD_READ, Character::READ_GROUP]],
            security: 'is_granted("ROLE_USER")',
            provider: LeaderboardProvider::class,
        ),
    ],
)]
// Other way is to put this info into ApiPlatform attribute tag OpenApi
#[QueryParameter(key: 'page', schema: ['type' => 'integer'], description: 'Page number')]
#[QueryParameter(key: 'name', schema: ['type' => 'string'], description: 'Character name')]
#[QueryParameter(key: 'rank', schema: ['type' => 'integer'], description: 'Rank search')]
class LeaderboardResponse
{
    public const string LEADERBOARD_READ = 'leaderboard:read';

    #[Groups([self::LEADERBOARD_READ])]
    public int $page;

    #[Groups([self::LEADERBOARD_READ])]
    public int $totalPages;

    #[Groups([self::LEADERBOARD_READ])]
    public int $totalItems;

    /**
     * @var LeaderboardEntry[]
     */
    #[Groups([self::LEADERBOARD_READ])]
    public array $items;

    /**
     * @param LeaderboardEntry[] $items
     */
    public function __construct(int $page, int $totalPages, int $totalItems, array $items)
    {
        $this->page = $page;
        $this->totalPages = $totalPages;
        $this->totalItems = $totalItems;
        $this->items = $items;
    }
}
