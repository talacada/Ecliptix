<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\AbstractApiTestCase;
use App\Factory\CharacterFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class LoginApiTest extends AbstractApiTestCase
{

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testLoginSuccessfulReturnsJwtTokenAndCharacter(): void
    {
        CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
            'username' => 'ShadowKnight',
            'email_verified' => true,
        ]);

        $client = static::createClient();

        $response = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'hero@ecliptix.com',
                'password' => 'password123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginFailsWithInvalidPassword(): void
    {
        CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
        ]);

        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'hero@ecliptix.com',
                'password' => 'wrong-password',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginFailsWhenEmailNotVerified(): void
    {
        CharacterFactory::new()->with([
            'email_verified' => false,
            'email' => 'unverified@ecliptix.com'
        ])->create();

        $client = static::createClient();

        $data = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'unverified@ecliptix.com',
                'password' => 'password123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $data = $data->toArray(false);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('Email is not verified', $data['description']);
    }

    public function testLoginFailsWithInvalidEmail(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'nonoexisting@ecliptix.com',
                'password' => 'password123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginFailsWithEmptyPayload(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [
            'json' => [
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testLoginFailsWithInvalidEmailFormat(): void
    {
        $client = static::createClient();

        $data = $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'notvalidemail.ecliptix.com',
                'password' => 'password123',
            ],
        ]);

         $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $data->toArray(false);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('email: This value is not a valid email address.', $data['description']);
    }
}
