<?php

declare(strict_types=1);

namespace App\Tests\Integration\Friends;

use App\Tests\Integration\AbstractApiTestCase;

class FriendsApiTest extends AbstractApiTestCase
{

    // TODO - testGetFriendsListReturnsCharactersFriends - Overit, ze GET /api/friends vrati seznam pratel prihlasene postavy
    // TODO - testAddFriendSuccessfulCreatesRelation - Overit, ze POST /api/friends/{id} prida druhou postavu do pratel
    // TODO - testAddFriendFailsWhenAddingSelf - Overit, ze nelze pridat do pratel sam sebe (400/422)
    // TODO - testAddFriendFailsWhenAlreadyFriends - Overit zamezeni duplicitniho pratelstvi (400/422)
    // TODO - testRemoveFriendDeletesRelation - Overit, ze DELETE /api/friends/{id} odstrani vazbu pratelstvi
    // TODO - testFriendsEndpointsRequireAuthentication - Overit 401 Unauthorized bez tokenu
}
