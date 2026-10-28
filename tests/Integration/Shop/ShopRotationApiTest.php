<?php

declare(strict_types=1);

namespace App\Tests\Integration\Shop;

use App\Factory\CharacterFactory;
use App\Factory\ItemDefinitionFactory;
use App\Tests\Integration\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ShopRotationApiTest extends AbstractApiTestCase
{
    public function testGetShopRotationGeneratesDailyRotationIfMissing(): void
    {
        $character = CharacterFactory::createOne();

        $client = static::createAuthenticatedClient($character);

        $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            'shopRotations' => [],
        ]);

        $client->request('GET', '/api/shop/rotation');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            'totalItems' => 1,
        ]);

        $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            'shopRotations' => ['/api/shop/shop_rotations/1'],
        ]);
    }

    // TODO - testGetShopRotationReturnsCurrentActiveOffers - Overit, ze GET /api/shop/rotation vrati aktivni nabidky rotace s cenami a atributy predmetu
    public function testGetShopRotationReturnsCurrentActiveOffers(): void
    {
        $character = CharacterFactory::createOne();
        ItemDefinitionFactory::createMany(80);

        $client = static::createAuthenticatedClient($character);

        $response = $client->request('GET', '/api/shop/rotation');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('shopOffers', $data['member'][0]);
        $this->assertCount(8, $data['member'][0]['shopOffers']);

        $this->assertSame('ShopOffer', $data['member'][0]['shopOffers'][0]['@type']);
        $this->assertSame(1, $data['member'][0]['shopOffers'][0]['id']);
        $this->assertArrayHasKey('item', $data['member'][0]['shopOffers'][0]);
    }

    public function testShopRotationRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/shop/rotation');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
