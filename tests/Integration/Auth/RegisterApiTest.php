<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\AbstractApiTestCase;
use App\Entity\Appearance\AppearanceTypeEnum;
use App\Factory\AppearanceOptionFactory;
use App\Factory\CharacterFactory;
use App\Factory\RaceFactory;
use App\Repository\Character\CharacterRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class RegisterApiTest extends AbstractApiTestCase
{

    public function testRegisterSuccessfulCreatesCharacter(): void
    {
        $race = RaceFactory::createOne();
        $hair = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::hair]);
        $eyes = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::eyes]);
        $mouth = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::mouth]);
        $nose = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::nose]);
        $ears = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::ears]);

        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'newplayer@ecliptix.com',
                'username' => 'MightyMage',
                'password' => 'SecurePass123!',
                'raceId' => $race->getId(),
                'hairId' => $hair->getId(),
                'eyesId' => $eyes->getId(),
                'mouthId' => $mouth->getId(),
                'noseId' => $nose->getId(),
                'earsId' => $ears->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $characterRepository = static::getContainer()->get(CharacterRepository::class);
        $character = $characterRepository->findOneBy(['email' => 'newplayer@ecliptix.com']);
        $this->assertNotNull($character);
        $this->assertSame('MightyMage', $character->getUsername());
        $this->assertFalse($character->isEmailVerified());
        $this->assertSame($race, $character->getRace());
        $this->assertSame($eyes, $character->getEyes());
        $this->assertSame($mouth, $character->getMouth());
        $this->assertSame($nose, $character->getNose());
        $this->assertSame($ears, $character->getEars());

    }

    public function testRegisterFailsWhenEmailAlreadyExists(): void
    {
        CharacterFactory::createOne(['email' => 'taken@ecliptix.com']);
        $race = RaceFactory::createOne();
        $hair = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::hair]);
        $eyes = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::eyes]);
        $mouth = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::mouth]);
        $nose = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::nose]);
        $ears = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::ears]);

        $client = static::createClient();

        $request = $client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'taken@ecliptix.com',
                'username' => 'UniqueName',
                'password' => 'SecurePass123!',
                'raceId' => $race->getId(),
                'hairId' => $hair->getId(),
                'eyesId' => $eyes->getId(),
                'mouthId' => $mouth->getId(),
                'noseId' => $nose->getId(),
                'earsId' => $ears->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = $request->toArray(false);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame('Email already registered', $data['detail']);
    }

    public function testRegisterFailsWhenUsernameAlreadyExists(): void
    {
        CharacterFactory::createOne(['username' => 'taken']);
        $race = RaceFactory::createOne();
        $hair = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::hair]);
        $eyes = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::eyes]);
        $mouth = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::mouth]);
        $nose = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::nose]);
        $ears = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::ears]);

        $client = static::createClient();

        $request = $client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'newplayer@ecliptix.com',
                'username' => 'taken',
                'password' => 'SecurePass123!',
                'raceId' => $race->getId(),
                'hairId' => $hair->getId(),
                'eyesId' => $eyes->getId(),
                'mouthId' => $mouth->getId(),
                'noseId' => $nose->getId(),
                'earsId' => $ears->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = $request->toArray(false);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame('username already registered', $data['detail']);
    }
    public function testRegisterFailsWithNonExistentAppearanceOption(): void
    {
        $race = RaceFactory::createOne();
        $eyes = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::eyes]);
        $mouth = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::mouth]);
        $nose = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::nose]);
        $ears = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::ears]);

        $client = static::createClient();

        $request = $client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'newplayer@ecliptix.com',
                'username' => 'UniqueName',
                'password' => 'SecurePass123!',
                'raceId' => $race->getId(),
                'hairId' => 1,
                'eyesId' => $eyes->getId(),
                'mouthId' => $mouth->getId(),
                'noseId' => $nose->getId(),
                'earsId' => $ears->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = $request->toArray(false);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame('Invalid hair_id', $data['detail']);
    }
    public function testRegisterFailsWhenAppearanceOptionBelongsToDifferentRace(): void
    {
        $race = RaceFactory::createOne();
        $raceTwo = RaceFactory::createOne();
        $eyes = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::eyes]);
        $hair = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::hair]);
        $mouth = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::mouth]);
        $nose = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::nose]);
        $ears = AppearanceOptionFactory::createOne(['race' => $raceTwo, 'type' => AppearanceTypeEnum::ears]);

        $client = static::createClient();

        $request = $client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'newplayer@ecliptix.com',
                'username' => 'UniqueName',
                'password' => 'SecurePass123!',
                'raceId' => $race->getId(),
                'hairId' => $hair->getId(),
                'eyesId' => $eyes->getId(),
                'mouthId' => $mouth->getId(),
                'noseId' => $nose->getId(),
                'earsId' => $ears->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = $request->toArray(false);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame('Invalid ears_id', $data['detail']);
    }

    public function testRegisterDispatchesVerificationEmail(): void
    {
        $race = RaceFactory::createOne();
        $eyes = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::eyes]);
        $hair = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::hair]);
        $mouth = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::mouth]);
        $nose = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::nose]);
        $ears = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::ears]);

        $client = static::createClient();

        $request = $client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'newplayer@ecliptix.com',
                'username' => 'UniqueName',
                'password' => 'SecurePass123!',
                'raceId' => $race->getId(),
                'hairId' => $hair->getId(),
                'eyesId' => $eyes->getId(),
                'mouthId' => $mouth->getId(),
                'noseId' => $nose->getId(),
                'earsId' => $ears->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        /** @var InMemoryTransport $transport */
        $transport = static::getContainer()->get('messenger.transport.async');

        $this->assertCount(1, $transport->getSent());

        $envelopes = $transport->getSent();
        $message = $envelopes[0]->getMessage();

        $this->assertInstanceOf(SendEmailMessage::class, $message);

        /** @var TemplatedEmail $email */
        $email = $message->getMessage();

        $this->assertSame('newplayer@ecliptix.com', $email->getTo()[0]->getAddress());
        $this->assertSame('Vítej v Ecliptixu — ověř svůj účet', $email->getSubject());

        $context = $email->getContext();
        $this->assertArrayHasKey('token', $context);
        $this->assertSame('UniqueName', $context['username']);
    }

    public function testRegisterFailsWithShortOrInvalidPassword(): void
    {
        $race = RaceFactory::createOne();
        $eyes = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::eyes]);
        $hair = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::hair]);
        $mouth = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::mouth]);
        $nose = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::nose]);
        $ears = AppearanceOptionFactory::createOne(['race' => $race, 'type' => AppearanceTypeEnum::ears]);

        $client = static::createClient();

        $request = $client->request('POST', '/api/auth/register', [
            'json' => [
                'email' => 'newplayer@ecliptix.com',
                'username' => 'UniqueName',
                'password' => 'short',
                'raceId' => $race->getId(),
                'hairId' => $hair->getId(),
                'eyesId' => $eyes->getId(),
                'mouthId' => $mouth->getId(),
                'noseId' => $nose->getId(),
                'earsId' => $ears->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $data = $request->toArray(false);
        $this->assertArrayHasKey('detail', $data);
        $this->assertSame('password: Password must be at least 8 characters long.', $data['detail']);
    }
}
