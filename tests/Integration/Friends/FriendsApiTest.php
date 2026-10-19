<?php

declare(strict_types=1);

namespace App\Tests\Integration\Friends;

use App\Factory\CharacterFactory;
use App\Tests\Integration\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;


class FriendsApiTest extends AbstractApiTestCase
{

    public function testGetFriendsListReturnsCharactersFriends()
    {
        $characterUs = CharacterFactory::createOne();
        $characterFriendOne = CharacterFactory::createOne();
        $characterFriendTwo = CharacterFactory::createOne();
        $characterFriendThree = CharacterFactory::createOne();

        $client = static::createAuthenticatedClient($characterUs);

        $client->request('POST', '/api/friends/' . $characterFriendOne->getId(), [
            'json' => [
                'container' => 'equipped',
            ]
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('POST', '/api/friends/' . $characterFriendTwo->getId(), [
            'json' => [
                'container' => 'equipped',
            ]
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('POST', '/api/friends/' . $characterFriendThree->getId(), [
            'json' => [
                'container' => 'equipped',
            ]
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJsonContains([
            '@type' => 'FriendRelation',
            'character' => '/api/character/' . $characterUs->getId(),
            'friend' => '/api/character/' . $characterFriendThree->getId(),
        ]);

        $client->request('GET', '/api/friends');


        $this->assertJsonContains([
            'totalItems' => 3,
            'member' => [
                [
                    'friend' => '/api/character/' . $characterFriendOne->getId(),
                ],
                [
                    'friend' => '/api/character/' . $characterFriendTwo->getId(),
                ],
                [
                    'friend' => '/api/character/' . $characterFriendThree->getId(),
                ],

            ]
        ]);

    }

    public function testAddFriendSuccessfulCreatesRelation(): void
    {
        $characterUs = CharacterFactory::createOne();
        $characterFriendOne = CharacterFactory::createOne();

        $client = static::createAuthenticatedClient($characterUs);

        $client->request('POST', '/api/friends/' . $characterFriendOne->getId(), [
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('GET', '/api/friends');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            'totalItems' => 1,
            'member' => [
                [
                    'friend' => '/api/character/' . $characterFriendOne->getId(),
                ],
            ]
        ]);

    }

    public function testAddFriendFailsWhenAddingSelf(): void
    {
        $characterUs = CharacterFactory::createOne();

        $client = static::createAuthenticatedClient($characterUs);

        $client->request('POST', '/api/friends/' . $characterUs->getId(), [
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertJsonContains([
            'detail' => 'You cannot add yourself.'
        ]);
    }

    public function testAddFriendFailsWhenAlreadyFriends(): void
    {
        $characterUs = CharacterFactory::createOne();
        $characterFriendOne = CharacterFactory::createOne();

        $client = static::createAuthenticatedClient($characterUs);

        $client->request('POST', '/api/friends/' . $characterFriendOne->getId(), [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('POST', '/api/friends/' . $characterFriendOne->getId(), [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertJsonContains([
            'detail' => 'You are already friends'
        ]);

    }

    public function testRemoveFriendDeletesRelation(): void
    {
        $characterUs = CharacterFactory::createOne();
        $characterFriendOne = CharacterFactory::createOne();

        $client = static::createAuthenticatedClient($characterUs);

        $response = $client->request('POST', '/api/friends/' . $characterFriendOne->getId(), [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('id', $data);
        $relationId = $data['id'];

        $client->request('DELETE', '/api/friends/' . $relationId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testFriendsEndpointsRequireAuthentication(): void
    {
        CharacterFactory::createOne();
        $characterFriendOne = CharacterFactory::createOne();

        $client = static::createClient();

        $client->request('POST', '/api/friends/' . $characterFriendOne->getId(), [
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $client->request('GET', '/api/friends');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $client->request('DELETE', '/api/friends/' . 1);
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
