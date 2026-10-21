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

    public function testGetLeaderboardFiltersByName(): void
    {
        CharacterFactory::createMany(40, fn(int $i) => [
            'prestigePoints' => 100 + $i,
        ]);

        $character = CharacterFactory::createOne([
            'username' => 'bbbb',
            'prestigePoints' => 90,
        ]);

        CharacterFactory::createMany(40, fn(int $i) => [
            'prestigePoints' => 10 + $i,
        ]);

        $client = static::createAuthenticatedClient($character);

        $response = $client->request('GET', '/api/leaderboard', [
            'query' => [
                'name' => 'bbbb',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);

        $this->assertSame(81, $data['totalItems']);
        $this->assertSame(16, $data['startRank']);
        $this->assertSame(66, $data['endRank']);
        $this->assertCount(51, $data['items']);

        $this->assertSame($character->getId(), $data['items'][25]['character']['id']);
        $this->assertSame(41, $data['items'][25]['rank']);
    }

    public function testGetLeaderboardFindsRankPosition(): void
    {
        CharacterFactory::createMany(40, fn(int $i) => [
            'prestigePoints' => 100 + $i,
        ]);

        $character = CharacterFactory::createOne([
            'username' => 'bbbb',
            'prestigePoints' => 90,
        ]);

        CharacterFactory::createMany(40, fn(int $i) => [
            'prestigePoints' => 10 + $i,
        ]);

        $client = static::createAuthenticatedClient($character);

        $response = $client->request('GET', '/api/leaderboard', [
            'query' => [
                'rank' => 41,
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);

        $this->assertSame(81, $data['totalItems']);
        $this->assertSame(16, $data['startRank']);
        $this->assertSame(66, $data['endRank']);
        $this->assertCount(51, $data['items']);

        $this->assertSame($character->getId(), $data['items'][25]['character']['id']);
        $this->assertSame(41, $data['items'][25]['rank']);

        $response = $client->request('GET', '/api/leaderboard', [
            'query' => [
                'rank' => 1,
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);

        $this->assertSame(81, $data['totalItems']);
        $this->assertSame(1, $data['startRank']);
        $this->assertSame(51, $data['endRank']);
        $this->assertCount(51, $data['items']);

        $response = $client->request('GET', '/api/leaderboard', [
            'query' => [
                'rank' => 70,
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);

        $this->assertSame(81, $data['totalItems']);
        $this->assertSame(31, $data['startRank']);
        $this->assertSame(81, $data['endRank']);
        $this->assertCount(51, $data['items']);
    }

    public function testLeaderboardRequiresAuthentication(): void
    {
        CharacterFactory::createMany(5);

        $client = static::createClient();

        $client->request('GET', '/api/leaderboard');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetLeaderboardReturns404WhenCharacterNameNotFound(): void
    {
        $character = CharacterFactory::createOne();
        $client = static::createAuthenticatedClient($character);

        $client->request('GET', '/api/leaderboard', [
            'query' => ['name' => 'non_existing_player'],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetLeaderboardReturns404WhenRankOutOfBounds(): void
    {
        $character = CharacterFactory::createOne();
        $client = static::createAuthenticatedClient($character);

        $client->request('GET', '/api/leaderboard', [
            'query' => ['rank' => 999],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
