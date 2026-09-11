<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\AbstractApiTestCase;

class RegisterOptionsApiTest extends AbstractApiTestCase
{

    // TODO - testGetRegisterOptionsReturnsAllRacesAndAppearances - Overit, ze verejny GET /api/auth/register/options vrati 200 OK se seznamem ras a moznosti vzhledu
    // TODO - testGetRegisterOptionsStructureMatchesDto - Overit spravnou strukturu odpovedi DTO (races, hair, eyes, mouth, nose, ears)
    // TODO - testGetRegisterOptionsGroupsOptionsByRace - Overit, ze moznosti vzhledu jsou spravne navazane na prislusne rasy
}
