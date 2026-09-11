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
        ])->_real();

        $jwtManager = static::getContainer()->get(JWTTokenManagerInterface::class);
        $token = $jwtManager->create($character);

        $client = static::createClient();

        $client->request('GET', '/api/character', [
            'auth_bearer' => $token,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'username' => 'Arthas',
            'gold' => 150,
        ]);
    }

    // TODO - testGetPublicCharacterReturnsPublicGroupOnly - Overit, ze GET /api/character/{id} vrati jen verejna data a nezverejni email/zlato
    // TODO - testGetPublicCharacterReturns404ForNonExistentId - Overit, ze neexistujici ID postavy vrati 404 Not Found
    // TODO - testPatchMineCharacterUpdatesUsernameAndAppearance - Overit, ze PATCH /api/character zmeni povolena pole prihlasene postavy
    // TODO - testPatchMineCharacterFailsWithDuplicateUsername - Overit, ze zmena jmena na jiz obsazene vrati 422 Unprocessable Entity
    // TODO - testDeleteMineCharacterDeletesUserAccount - Overit, ze DELETE /api/character smaze ucet prihlaseneho uzivatele
}
