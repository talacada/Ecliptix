<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Factory\CharacterFactory;
use App\Factory\PasswordResetTokenFactory;
use App\Tests\Integration\AbstractApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetApiTest extends AbstractApiTestCase
{
    public function testPasswordResetSuccessfulChangesPassword(): void
    {
        $character = CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
        ]);

        $token = PasswordResetTokenFactory::createOne([
            'character' => $character,
        ]);

        $client = static::createClient();

        $data = $client->request('POST', '/api/auth/password-reset', [
            'json' => [
                'password' => 'NewPassword1',
                'token' => $token->getToken(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $data->toArray(false);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('Password reset successfully.', $data['message']);

        // Token cant be used again

        $client->request('POST', '/api/auth/password-reset', [
            'json' => [
                'password' => 'NewPassword1',
                'token' => $token->getToken(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        // Old password not working

        $response = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'hero@ecliptix.com',
                'password' => 'password123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // Login with new password OK

        $response = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'hero@ecliptix.com',
                'password' => 'NewPassword1',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testPasswordResetFailsWithNonExistingToken(): void
    {
        CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
            'email_verified' => true,
        ]);

        $client = static::createClient();

        $client->request('POST', '/api/auth/password-reset', [
            'json' => [
                'password' => 'NewPassword',
                'token' => '8bea0ac7-27a4-484b-b89c-3c3f76ce05d5',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
    public function testPasswordResetFailsWithExpiredToken(): void
    {
        $character = CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
            'email_verified' => true,
        ]);

        $token = PasswordResetTokenFactory::createOne([
            'character' => $character,
            'expires_at' => new DateTimeImmutable('-1 hour'),
        ]);

        $client = static::createClient();

        $client->request('POST', '/api/auth/password-reset', [
            'json' => [
                'password' => 'NewPassword',
                'token' => $token->getToken(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
    public function testPasswordResetFailsWithAlreadyUsedToken(): void
    {
        $character = CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
            'email_verified' => true,
        ]);

        $token = PasswordResetTokenFactory::createOne([
            'character' => $character,
            'used_at' => new DateTimeImmutable('-1 hour'),
        ]);

        $client = static::createClient();

        $client->request('POST', '/api/auth/password-reset', [
            'json' => [
                'password' => 'NewPassword',
                'token' => $token->getToken(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPasswordResetFailsWithShortPassword(): void
    {
        CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
            'email_verified' => true,
        ]);

        $client = static::createClient();

        $data = $client->request('POST', '/api/auth/password-reset', [
            'json' => [
                'password' => 'short',
                'token' => '8bea0ac7-27a4-484b-b89c-3c3f76ce05d5',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $data->toArray(false);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('password: Password must be at least 8 characters long.', $data['description']);
    }

    public function testPasswordResetFailsWithInvalidUuidFormat(): void
    {
        CharacterFactory::createOne();
        $client = static::createClient();

        $data = $client->request('POST', '/api/auth/password-reset', [
            'json' => [
                'password' => 'PasswordPassword2',
                'token' => 'not-uuid',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $data->toArray(false);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('token: Token is not valid', $data['description']);
    }
}
