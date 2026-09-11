<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\AbstractApiTestCase;

class VerifyEmailApiTest extends AbstractApiTestCase
{

    // TODO - testVerifyEmailSuccessfulActivatesCharacter - Overit, ze platny token aktivuje email_verified na true a nastavi used_at
    // TODO - testVerifyEmailFailsWithExpiredToken - Overit, ze prosly token vrati 400/422 chybu
    // TODO - testVerifyEmailFailsWithAlreadyUsedToken - Overit, ze jiz pouzity token nelze znovu uplatnit
    // TODO - testVerifyEmailFailsWithInvalidTokenFormat - Overit, ze neplatne UUID tokenu vrati 404/422 chybu
}
