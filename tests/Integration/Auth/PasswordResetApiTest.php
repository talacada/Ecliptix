<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Factory\CharacterFactory;
use App\Factory\PasswordResetTokenFactory;
use App\Tests\Integration\AbstractApiTestCase;
use DateTime;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class PasswordResetApiTest extends AbstractApiTestCase
{
    // TODO - testPasswordResetSuccessfulChangesPassword - Overit, ze s platnym tokenem se zmeni heslo postavy a token se oznaci jako pouzity
    public function testPasswordResetSuccessfulChangesPassword(): void
    {

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
    public function testPasswordResetFailsWithWrongToken(): void
    {
        $character = CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
            'email_verified' => true,
        ]);

        PasswordResetTokenFactory::createOne([
            'character' => $character,
            'expires_at' => new DateTimeImmutable('-1 hour'),
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
    public function testPasswordResetFailsWithAlreadyUsedToken(): void
    {
        $character = CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
            'email_verified' => true,
        ]);

        PasswordResetTokenFactory::createOne([
            'character' => $character,
            'used_at' => new DateTimeImmutable('-1 hour'),
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
}
