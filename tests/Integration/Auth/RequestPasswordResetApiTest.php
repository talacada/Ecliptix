<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\AbstractApiTestCase;

class RequestPasswordResetApiTest extends AbstractApiTestCase
{

    // TODO - testRequestPasswordResetGeneratesTokenAndDispatchesEmail - Overit, ze pro existujici email vznikne PasswordResetToken a odesle se email
    // TODO - testRequestPasswordResetHandlesGracefullyNonExistentEmail - Overit bezpecne chovani pri neexistujicim emailu (neprozrazovat existenci uctu)
    // TODO - testRequestPasswordResetFailsWithInvalidEmailFormat - Overit validacni chybu pri neplatnem tvaru emailu
}
