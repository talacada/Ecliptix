<?php

declare(strict_types=1);

namespace App\Tests\Integration\Character;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Enum\GameCostEnum;
use App\Factory\AppearanceOptionFactory;
use App\Factory\RaceFactory;
use App\Tests\Integration\AbstractApiTestCase;
use App\Factory\CharacterFactory;
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

    public function testPatchMineCharacterUpdatesUsernameAndAppearance(): void
    {
        $character = CharacterFactory::createOne([
            'diamonds' => 10
        ]);

        $client = static::createAuthenticatedClient($character);

        $response = $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);
        $this->assertSame($data['username'], $character->getUsername());
        $this->assertSame($data['race'], '/api/races/' . $character->getRace()->getId());
        $this->assertSame($data['hair'], '/api/appearance_options/' . $character->getHair()->getId());
        $this->assertSame($data['eyes'], '/api/appearance_options/' . $character->getEyes()->getId());
        $this->assertSame($data['mouth'], '/api/appearance_options/' . $character->getMouth()->getId());
        $this->assertSame($data['nose'], '/api/appearance_options/' . $character->getNose()->getId());
        $this->assertSame($data['ears'], '/api/appearance_options/' . $character->getEars()->getId());
        $this->assertSame($data['diamonds'], $character->getDiamonds());


        $oldDiamonds = $character->getDiamonds();
        $username = 'newUsername';
        $race = RaceFactory::createOne();
        $hair = AppearanceOptionFactory::createOne([
            'race' => $race,
            'type' => AppearanceTypeEnum::hair,
        ]);
        $eyes = AppearanceOptionFactory::createOne([
            'race' => $race,
            'type' => AppearanceTypeEnum::eyes,
        ]);
        $mouth = AppearanceOptionFactory::createOne([
            'race' => $race,
            'type' => AppearanceTypeEnum::mouth,
        ]);
        $nose = AppearanceOptionFactory::createOne([
            'race' => $race,
            'type' => AppearanceTypeEnum::nose,
        ]);
        $ears = AppearanceOptionFactory::createOne([
            'race' => $race,
            'type' => AppearanceTypeEnum::ears,
        ]);

        $response = $client->request('PATCH', '/api/character',[
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'username' => $username,
                'race' => '/api/races/' . $race->getId(),
                'hair' => '/api/appearance_options/' . $hair->getId(),
                'eyes' => '/api/appearance_options/' . $eyes->getId(),
                'mouth' => '/api/appearance_options/' . $mouth->getId(),
                'nose' => '/api/appearance_options/' . $nose->getId(),
                'ears' => '/api/appearance_options/' . $ears->getId(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);
        $this->assertSame($data['username'], $username);
        $this->assertSame($data['race'], '/api/races/' . $race->getId());
        $this->assertSame($data['hair'], '/api/appearance_options/' . $hair->getId());
        $this->assertSame($data['eyes'], '/api/appearance_options/' . $eyes->getId());
        $this->assertSame($data['mouth'], '/api/appearance_options/' . $mouth->getId());
        $this->assertSame($data['nose'], '/api/appearance_options/' . $nose->getId());
        $this->assertSame($data['ears'], '/api/appearance_options/' . $ears->getId());
        $this->assertSame($oldDiamonds - GameCostEnum::CHANGE_APPEARANCE->getAmount(), $data['diamonds']);
    }
    public function testPatchMineCharacterFailsWithDuplicateUsername(): void
    {
        $characterRareUsername = CharacterFactory::createOne([
            'username' => 'SUPER_RARE_USERNAME'
        ]);

        $character = CharacterFactory::createOne([
            'username' => 'normal',
            'diamonds' => 10
        ]);

        $client = static::createAuthenticatedClient($character);

        $client->request('PATCH', '/api/character',[
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'username' => $characterRareUsername->getUsername(),
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response = $client->request('GET', '/api/character');

        $data = $response->toArray(false);
        $this->assertSame('normal', $data['username']);
        $this->assertSame(10, $data['diamonds']);
    }

    public function testDeleteMineCharacterDeletesUserAccount(): void
    {
        $character = CharacterFactory::new()->withPassword('TestPassword123')->create();

        $clientNotAuth = static::createClient();
        $clientNotAuth->request('DELETE', '/api/character');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $client = static::createAuthenticatedClient($character);
        $client->request('DELETE', '/api/character');
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->request('GET', '/api/character');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $request = $clientNotAuth->request('POST', '/api/auth/login', [
            'json' => [
                'email' => $character->getEmail(),
                'password' => 'TestPassword123',
            ],
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $data = $request->toArray(false);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame('Invalid credentials', $data['detail']);

        $this->assertNull(CharacterFactory::repository()->first(['id' => $character->getId()]));
    }

    //TODO EmptyPatch wont take dia
    //TODO GET /api/character/{id} without JWT
    //TODO PATCH /api/character without JWT
}
