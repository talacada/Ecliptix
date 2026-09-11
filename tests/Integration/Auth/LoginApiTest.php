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

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'character' => [
                'username' => 'ShadowKnight',
            ],
        ]);

        $data = $response->toArray();
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginFailsWithInvalidPassword(): void
    {
        CharacterFactory::createOne([
            'email' => 'hero@ecliptix.com',
            'email_verified' => true,
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
        CharacterFactory::new()->unverified()->create([
            'email' => 'unverified@ecliptix.com',
        ]);

        $client = static::createClient();

        $client->request('POST', '/api/auth/login', [
            'json' => [
                'email' => 'unverified@ecliptix.com',
                'password' => 'password123',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    // TODO - testLoginFailsWhenUserDoesNotExist - Overit, ze prihlaseni s neexistujicim emailem vrati 401 Unauthorized
    // TODO - testLoginFailsWithEmptyPayload - Overit, ze odeslani prazdneho JSON body vrati 400/422 validacni chybu
    // TODO - testLoginFailsWithInvalidEmailFormat - Overit, ze neplatny format emailu zpusobi validacni chybu
}
