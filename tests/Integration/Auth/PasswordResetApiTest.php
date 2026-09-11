<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\AbstractApiTestCase;

class PasswordResetApiTest extends AbstractApiTestCase
{

    // TODO - testPasswordResetSuccessfulChangesPassword - Overit, ze s platnym tokenem se zmeni heslo postavy a token se oznaci jako pouzity
    // TODO - testPasswordResetFailsWithExpiredToken - Overit, ze vyprseny token vrati 400/422 chybu
    // TODO - testPasswordResetFailsWithAlreadyUsedToken - Overit, ze jiz pouzity token nelze zopakovat
    // TODO - testPasswordResetFailsWithShortPassword - Overit validaci minimalni delky noveho hesla
}
