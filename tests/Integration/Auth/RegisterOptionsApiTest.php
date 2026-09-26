<?php

declare(strict_types=1);

namespace App\Tests\Integration\Auth;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Factory\AppearanceOptionFactory;
use App\Factory\RaceFactory;
use App\Tests\Integration\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegisterOptionsApiTest extends AbstractApiTestCase
{
    public function testGetRegisterOptionsReturnsAllRacesAndAppearances(): void
    {
        $race1 = RaceFactory::createOne();
        AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::eyes]);
        AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::hair]);
        AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::mouth]);
        AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::nose]);
        AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::ears]);

        $race2 = RaceFactory::createOne();
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::eyes]);
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::hair]);
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::ears]);
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::ears]);
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::ears]);

        $client = static::createClient();

        $request = $client->request('GET', '/api/auth/register/options');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $request->toArray(false);
        $this->assertArrayHasKey('@type', $data);
        $this->assertSame('RegisterOptionsResponse', $data['@type']);

        $this->assertJsonContains([
            '@type' => 'RegisterOptionsResponse',
            'races' => [
                [
                    'id' => $race1->getId(),
                    'name' => $race1->getName(),
                ],
                [
                    'id' => $race2->getId(),
                    'name' => $race2->getName(),
                ],
            ],
        ]);


        $this->assertCount(1, $data['races'][0]['appearance']['eyes']);
        $this->assertCount(1, $data['races'][0]['appearance']['hair']);
        $this->assertCount(1, $data['races'][0]['appearance']['mouth']);
        $this->assertCount(1, $data['races'][0]['appearance']['nose']);
        $this->assertCount(1, $data['races'][0]['appearance']['ears']);

        $this->assertCount(7, $data['races'][1]['appearance']);
        $this->assertCount(1, $data['races'][1]['appearance']['eyes']);
        $this->assertCount(1, $data['races'][1]['appearance']['hair']);
        $this->assertCount(0, $data['races'][1]['appearance']['mouth']);
        $this->assertCount(0, $data['races'][1]['appearance']['nose']);
        $this->assertCount(3, $data['races'][1]['appearance']['ears']);
    }
    // TODO - testGetRegisterOptionsStructureMatchesDto - Overit spravnou strukturu odpovedi DTO (races, hair, eyes, mouth, nose, ears)
    public function testGetRegisterOptionsStructureMatchesDto(): void
    {

    }

    // TODO - testGetRegisterOptionsGroupsOptionsByRace - Overit, ze moznosti vzhledu jsou spravne navazane na prislusne rasy
    public function testGetRegisterOptionsGroupsOptionsByRace(): void
    {

    }
}
