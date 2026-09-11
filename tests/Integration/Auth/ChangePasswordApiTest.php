<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\AbstractApiTestCase;

class ChangePasswordApiTest extends AbstractApiTestCase
{

    // TODO - testChangePasswordRequiresAuthentication - Overit, ze neautentizovany pozadavek na POST /api/auth/change-password vrati 401 Unauthorized
    // TODO - testChangePasswordSuccessfulUpdatesPasswordHash - Overit, ze prihlaseny uzivatel s platnym starym heslem uspesne zmeni heslo
    // TODO - testChangePasswordFailsWithWrongOldPassword - Overit, ze nespravne stare heslo vrati 400/422 chybu
    // TODO - testChangePasswordFailsWhenNewPasswordSameAsOld - Overit validacni pravidlo zabranujici pouziti stejneho hesla
}
