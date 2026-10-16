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

        $characterUs->addFriend($characterFriendOne);
        $characterUs->addFriend($characterFriendTwo);
        $characterUs->addFriend($characterFriendThree);

        $client = static::createAuthenticatedClient($characterUs);

        $client->request('GET', '/api/friends');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            'data' => []
        ]);

    }
    // TODO - testGetFriendsListReturnsCharactersFriends - Overit, ze GET /api/friends vrati seznam pratel prihlasene postavy
    // TODO - testAddFriendSuccessfulCreatesRelation - Overit, ze POST /api/friends/{id} prida druhou postavu do pratel
    // TODO - testAddFriendFailsWhenAddingSelf - Overit, ze nelze pridat do pratel sam sebe (400/422)
    // TODO - testAddFriendFailsWhenAlreadyFriends - Overit zamezeni duplicitniho pratelstvi (400/422)
    // TODO - testRemoveFriendDeletesRelation - Overit, ze DELETE /api/friends/{id} odstrani vazbu pratelstvi
    // TODO - testFriendsEndpointsRequireAuthentication - Overit 401 Unauthorized bez tokenu
}
