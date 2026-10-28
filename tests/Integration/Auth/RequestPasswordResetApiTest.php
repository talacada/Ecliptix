<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Factory\CharacterFactory;
use App\Tests\Integration\AbstractApiTestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class RequestPasswordResetApiTest extends AbstractApiTestCase
{
    public function testRequestPasswordResetGeneratesTokenAndDispatchesEmail(): void
    {
        $character = CharacterFactory::createOne([
            'email' => 'player@ecliptix.com',
            'username' => 'MightyPlayer',
        ]);

        $client = static::createClient();

        $client->request('POST', '/api/auth/request-password-reset', [
            'json' => [
                'email' => $character->getEmail(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        /** @var InMemoryTransport $transport */
        $transport = static::getContainer()->get('messenger.transport.async');

        $this->assertCount(1, $transport->getSent());

        $envelopes = $transport->getSent();
        $message = $envelopes[0]->getMessage();

        $this->assertInstanceOf(SendEmailMessage::class, $message);

        /** @var TemplatedEmail $email */
        $email = $message->getMessage();

        $this->assertSame('player@ecliptix.com', $email->getTo()[0]->getAddress());
        $this->assertSame('Ecliptix — Password Reset', $email->getSubject());

        $context = $email->getContext();
        $this->assertArrayHasKey('token', $context);
        $this->assertSame('MightyPlayer', $context['username']);
    }

    public function testRequestPasswordResetHandlesGracefullyNonExistentEmail(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/api/auth/request-password-reset', [
            'json' => [
                'email' => 'emailNotExist@gmail.com',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('message', $data);
        $this->assertSame('If the email exists, a reset link has been sent.', $data['message']);

        // Check if no mail was send
        /** @var InMemoryTransport $transport */
        $transport = static::getContainer()->get('messenger.transport.async');
        $this->assertCount(0, $transport->getSent());
    }

    public function testRequestPasswordResetFailsWithInvalidEmailFormat(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/api/auth/request-password-reset', [
            'json' => [
                'email' => 'notAValidEmail',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('email: Email should be a valid email address.', $data['description']);
    }
}
