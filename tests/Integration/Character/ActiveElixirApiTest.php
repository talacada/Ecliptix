<?php

declare(strict_types=1);

namespace App\Tests\Integration\Character;

use App\Factory\ActiveElixirFactory;
use App\Factory\CharacterFactory;
use App\Tests\Integration\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ActiveElixirApiTest extends AbstractApiTestCase
{
    public function testGetActiveElixirReturnsDetails(): void
    {
        $character = CharacterFactory::createOne();
        $elixir = ActiveElixirFactory::createOne([
            'character' => $character,
        ]);

        $client = $this->createAuthenticatedClient($character);

        $response = $client->request('GET', '/api/character/elixir/' . $elixir->getId());

        $data = $response->toArray();
        $this->assertArrayHasKey('remainingSeconds', $data);
        $this->assertGreaterThanOrEqual(3500, $data['remainingSeconds']);

        $this->assertArrayHasKey('name', $data);
        $this->assertSame($elixir->getItemDefinition()->getName(), $data['name']);
    }
    public function testDeleteActiveElixirRemovesBuff(): void
    {
        $character = CharacterFactory::createOne();
        $elixir = ActiveElixirFactory::createOne([
            'character' => $character,
        ]);

        $client = $this->createAuthenticatedClient($character);

        $response = $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('activeElixirs', $data);
        $this->assertNotEmpty($data['activeElixirs']);

        $client->request('DELETE', '/api/character/elixir/' . $elixir->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $response = $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('activeElixirs', $data);
        $this->assertEmpty($data['activeElixirs']);

    }
    public function testCannotDeleteAnotherCharactersActiveElixir(): void
    {
        $character = CharacterFactory::createOne();
        $elixir = ActiveElixirFactory::createOne([
            'character' => $character,
        ]);

        $characterThief = CharacterFactory::createOne();

        $client = $this->createAuthenticatedClient($characterThief);

        $response = $client->request('DELETE', '/api/character/elixir/' . $elixir->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('Not Found', $data['description']);

    }
    public function testActiveElixirEndpointsRequireAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/character/elixir/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

    }
}
