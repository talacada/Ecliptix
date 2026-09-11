<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Tests\Integration\AbstractApiTestCase;
use App\Entity\Appearance\AppearanceTypeEnum;
use App\Factory\AppearanceOptionFactory;
use App\Factory\CharacterFactory;
use App\Factory\RaceFactory;
use App\Repository\Character\CharacterRepository;
use Symfony\Component\HttpFoundation\Response;

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

        $client->request('POST', '/api/auth/register', [
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
    }

    // TODO - testRegisterFailsWhenUsernameAlreadyExists - Overit, ze registrace s obsazenym username vrati 422 Unprocessable Entity
    // TODO - testRegisterFailsWithNonExistentAppearanceOption - Overit, ze neexistujici ID vzhledu vrati validacni/business chybu
    // TODO - testRegisterFailsWhenAppearanceOptionBelongsToDifferentRace - Overit, ze nelze kombinovat rasu s moznostmi vzhledu jine rasy
    // TODO - testRegisterDispatchesVerificationEmail - Overit, ze po registraci byl odeslan email s verifikacnim odkazem do Messenger busu
    // TODO - testRegisterFailsWithShortOrInvalidPassword - Overit selhani registrace na validacnich pravidlech hesla
}
