<?php

declare(strict_types=1);

namespace App\Tests\Integration\Leaderboard;

use App\Tests\Integration\AbstractApiTestCase;

class LeaderboardApiTest extends AbstractApiTestCase
{

    // TODO - testGetLeaderboardReturnsPaginatedRankedCharacters - Overit, ze GET /api/leaderboard vrati zebricek serazeny podle urovne/prestige
    // TODO - testGetLeaderboardFiltersByName - Overit filtrovani a vyhledavani postavy podle parametru ?name=...
    // TODO - testGetLeaderboardFindsRankPosition - Overit vyhledani umisteni postavy podle parametru ?rank=...
    // TODO - testLeaderboardRequiresAuthentication - Overit 401 Unauthorized bez platneho tokenu
}
