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
        $character = CharacterFactory::new()->create();
        $token = EmailVerificationTokenFactory::createOne([
            'character' => $character,
        ]);

        $client = static::createClient();

        $request = $client->request('POST', '/api/auth/verify-email', [
            'json' => [
                'token' => $token->getToken(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $request->toArray(false);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Email verified.', $data['message']);

        $request = $client->request('POST', '/api/auth/verify-email', [
            'json' => [
                'token' => $token->getToken(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $request->toArray(false);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame('Invalid or expired token', $data['detail']);

        $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => $character->getEmail(),
                'password' => 'password123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

    }
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
    public function testVerifyEmailFailsWithAlreadyUsedToken(): void
    {
        $character = CharacterFactory::new()->create();
        $token = EmailVerificationTokenFactory::createOne([
            'character' => $character,
            'used_at' => new DateTimeImmutable('-1 hour'),
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
    public function testVerifyEmailFailsWithInvalidTokenFormat(): void
    {
        $client = static::createClient();

        $request = $client->request('POST', '/api/auth/verify-email', [
            'json' => [
                'token' => 'invalid-token',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = $request->toArray(false);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame('The data is not a valid "Symfony\Component\Uid\Uuid" string representation.', $data['detail']);
    }
}
