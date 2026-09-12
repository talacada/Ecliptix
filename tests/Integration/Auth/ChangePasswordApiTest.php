<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Factory\CharacterFactory;
use App\Tests\Integration\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ChangePasswordApiTest extends AbstractApiTestCase
{
    /**
     * @throws TransportExceptionInterface
     */
    public function testChangePasswordRequiresAuthentication(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/api/auth/change-password', [
            'json' => [
                'oldPassword' => 'oldPassword',
                'newPassword' => 'newPassword',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('JWT Token not found', $data['message']);
    }

    public function testChangePasswordFailsWithWrongOldPassword(): void
    {
        $character = CharacterFactory::new()->withPassword('TestPassword123')->create();
        $client = $this->createAuthenticatedClient($character);

        $response = $client->request('POST', '/api/auth/change-password', [
            'json' => [
                'oldPassword' => 'WRONGPASSWORD',
                'newPassword' => 'newPassword',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('Old password is incorrect', $data['description']);
    }

    public function testChangePasswordFailsWhenNewPasswordSameAsOld(): void
    {
        $character = CharacterFactory::new()->withPassword('TestPassword123')->create();
        $client = $this->createAuthenticatedClient($character);

        $response = $client->request('POST', '/api/auth/change-password', [
            'json' => [
                'oldPassword' => 'TestPassword123',
                'newPassword' => 'aa1@',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('newPassword: This value is too short. It should have 8 characters or more.', $data['description']);
    }

    // TODO - testChangePasswordSuccessfulUpdatesPasswordHash - Overit, ze prihlaseny uzivatel s platnym starym heslem uspesne zmeni heslo
}
