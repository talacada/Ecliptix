<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Factory\CharacterFactory;
use App\Tests\Integration\AbstractApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use App\Factory\EmailVerificationTokenFactory;

class VerifyEmailApiTest extends AbstractApiTestCase
{
    // TODO - testVerifyEmailSuccessfulActivatesCharacter - Overit, ze platny token aktivuje email_verified na true a nastavi used_at
    public function testVerifyEmailSuccessfulActivatesCharacter(): void
    {

    }
    // TODO - testVerifyEmailFailsWithExpiredToken - Overit, ze prosly token vrati 400/422 chybu
    public function testVerifyEmailFailsWithExpiredToken(): void
    {
        $character = CharacterFactory::new()->create();
        $token = EmailVerificationTokenFactory::createOne([
            'character' => $character,
            'expires_at' => new DateTimeImmutable('- 1hours'),
            ]);

        $client = static::createClient();

        $request = $client->request('POST', '/api/auth/verify-email', [
            'json' => [
                'token' => $token->getToken(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $request->toArray(false);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame('Invalid or expired token', $data['detail']);
    }
    // TODO - testVerifyEmailFailsWithAlreadyUsedToken - Overit, ze jiz pouzity token nelze znovu uplatnit
    // TODO - testVerifyEmailFailsWithInvalidTokenFormat - Overit, ze neplatne UUID tokenu vrati 404/422 chybu
}
