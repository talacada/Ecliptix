<?php

declare(strict_types=1);

namespace App\Tests\Integration\Character;

use App\Tests\Integration\AbstractApiTestCase;

class ActiveElixirApiTest extends AbstractApiTestCase
{

    // TODO - testGetActiveElixirReturnsDetails - Overit, ze GET /api/character/elixir/{id} vrati platnost a bonus aktivniho elixir postavy
    // TODO - testDeleteActiveElixirRemovesBuff - Overit, ze DELETE /api/character/elixir/{id} odstrani aktivni elixir z postavy
    // TODO - testCannotDeleteAnotherCharactersActiveElixir - Overit, ze cizi aktivni elixir nelze smazat (403/404)
    // TODO - testActiveElixirEndpointsRequireAuthentication - Overit 401 Unauthorized pro neautentizovane pozadavky
}
