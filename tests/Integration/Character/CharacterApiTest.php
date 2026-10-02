<?php

declare(strict_types=1);

namespace App\Tests\Integration\Character;

use App\Tests\Integration\AbstractApiTestCase;
use App\Factory\CharacterFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CharacterApiTest extends AbstractApiTestCase
{

    public function testGetMineCharacterRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetMineCharacterWithValidJwtReturnsCharacterData(): void
    {
        $character = CharacterFactory::createOne([
            'username' => 'Arthas',
            'gold' => 150,
        ]);

        $client = static::createAuthenticatedClient($character);

        $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'username' => 'Arthas',
            'gold' => 150,
        ]);
    }

    public function testGetPublicCharacterReturnsPublicGroupOnly(): void
    {
        $characterEnemy = CharacterFactory::createOne();
        $characterUs = CharacterFactory::createOne();

        $client = static::createAuthenticatedClient($characterUs);
        $request = $client->request('GET', '/api/character/' . $characterEnemy->getId());

        // This and more are public info
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'username' => $characterEnemy->getUsername(),
            'level' => $characterEnemy->getLevel(),
            'experience' => $characterEnemy->getExperience(),
            'damage' => $characterEnemy->getDamage(),
        ]);

        // This is private info, and other characters cant see it.
        $data = $request->toArray(false);
        $this->assertArrayNotHasKey('gold', $data);
        $this->assertArrayNotHasKey('email', $data);
        $this->assertArrayNotHasKey('diamonds', $data);
        $this->assertArrayNotHasKey('shopRotations', $data);
        $this->assertArrayNotHasKey('characterInventories', $data);
        $this->assertArrayNotHasKey('backpackCapacity', $data);
        $this->assertArrayNotHasKey('friendsCollection', $data);
        $this->assertArrayNotHasKey('email_verified', $data);

    }

    public function testGetPublicCharacterReturns404ForNonExistentId(): void
    {
        $character = CharacterFactory::createOne();

        $client = static::createAuthenticatedClient($character);
        $client->request('GET', '/api/character/1111');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertJsonContains([
            'detail' => "Character not found",
        ]);

    }

    // TODO - testPatchMineCharacterUpdatesUsernameAndAppearance - Overit, ze PATCH /api/character zmeni povolena pole prihlasene postavy
    // TODO - testPatchMineCharacterFailsWithDuplicateUsername - Overit, ze zmena jmena na jiz obsazene vrati 422 Unprocessable Entity
    // TODO - testDeleteMineCharacterDeletesUserAccount - Overit, ze DELETE /api/character smaze ucet prihlaseneho uzivatele
}
