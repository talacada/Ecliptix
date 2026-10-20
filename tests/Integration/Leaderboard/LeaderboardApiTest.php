<?php

declare(strict_types=1);

namespace App\Tests\Integration\Leaderboard;

use App\Factory\CharacterFactory;
use App\Tests\Integration\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class LeaderboardApiTest extends AbstractApiTestCase
{
    public function testGetLeaderboardReturnsPaginatedRankedCharacters(): void
    {
        CharacterFactory::createOne(['prestigePoints' => 90, 'username' => 'bbbb']);
        CharacterFactory::createOne(['prestigePoints' => 90, 'username' => 'aaaa']);
        CharacterFactory::createOne(['prestigePoints' => 30,]);
        CharacterFactory::createOne(['prestigePoints' => 29,]);
        CharacterFactory::createOne(['prestigePoints' => 28,]);
        $character = CharacterFactory::createOne(['prestigePoints' => 100]);

        $client = static::createAuthenticatedClient($character);

        $response = $client->request('GET', '/api/leaderboard');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('totalItems', $data);
        $this->assertSame(6, $data['totalItems']);

        $this->assertArrayHasKey('items', $data);
        $this->assertCount(6, $data['items']);

        $this->assertSame('LeaderboardEntry', $data['items'][0]['@type']);
        $this->assertSame(1, $data['items'][0]['rank']);
        $this->assertSame($character->getId(), $data['items'][0]['character']['id']);

        $prevPP = null;
        $prevRank = null;
        $prevName = null;
        foreach ($data['items'] as $item) {
            if ($prevPP != null) {
                $this->assertLessThanOrEqual($prevPP, $item['character']['prestigePoints']);
                $this->assertGreaterThanOrEqual($prevRank, $item['rank']);

                if ($prevPP === $item['character']['prestigePoints']) {
                    $this->assertTrue($prevName < $item['character']['username']);
                }
            }
            $prevPP = $item['character']['prestigePoints'];
            $prevRank = $item['rank'];
            $prevName = $item['character']['username'];
        }
    }

    // TODO - testGetLeaderboardFiltersByName - Overit filtrovani a vyhledavani postavy podle parametru ?name=...
    public function testGetLeaderboardFiltersByName(): void
    {

    }

    // TODO - testGetLeaderboardFindsRankPosition - Overit vyhledani umisteni postavy podle parametru ?rank=...
    public function testGetLeaderboardFindsRankPosition(): void
    {

    }

    // TODO - testLeaderboardRequiresAuthentication - Overit 401 Unauthorized bez platneho tokenu
    public function testLeaderboardRequiresAuthentication(): void
    {
        CharacterFactory::createMany(5);

        $client = static::createClient();

        $client->request('GET', '/api/leaderboard');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
