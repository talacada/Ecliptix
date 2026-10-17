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
            'json' => [
                'container' => 'equipped',
            ]
        ]);
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('GET', '/api/friends');


        $this->assertJsonContains([
            'totalItems' => 1,
            'member' => [
                [
                    'friend' => '/api/character/' . $characterFriendOne->getId(),
                ],
            ]
        ]);

    }

    // TODO - testAddFriendSuccessfulCreatesRelation - Overit, ze POST /api/friends/{id} prida druhou postavu do pratel
    // TODO - testAddFriendFailsWhenAddingSelf - Overit, ze nelze pridat do pratel sam sebe (400/422)
    // TODO - testAddFriendFailsWhenAlreadyFriends - Overit zamezeni duplicitniho pratelstvi (400/422)
    // TODO - testRemoveFriendDeletesRelation - Overit, ze DELETE /api/friends/{id} odstrani vazbu pratelstvi
    // TODO - testFriendsEndpointsRequireAuthentication - Overit 401 Unauthorized bez tokenu
}
