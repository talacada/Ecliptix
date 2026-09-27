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
        $eyes = AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::eyes]);
        $hair = AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::hair]);
        $mouth = AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::mouth]);
        $nose = AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::nose]);
        $ears = AppearanceOptionFactory::createOne(['race' => $race1, 'type' => AppearanceTypeEnum::ears]);

        $race2 = RaceFactory::createOne();
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::eyes]);
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::hair]);
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::ears, 'sort_order' => 5]);
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::ears, 'sort_order' => 1]);
        AppearanceOptionFactory::createOne(['race' => $race2, 'type' => AppearanceTypeEnum::ears, 'sort_order' => 3]);

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
        $this->assertSame($eyes->getId(), $data['races'][0]['appearance']['eyes'][0]['id']);
        $this->assertCount(1, $data['races'][0]['appearance']['hair']);
        $this->assertSame($hair->getId(), $data['races'][0]['appearance']['hair'][0]['id']);
        $this->assertCount(1, $data['races'][0]['appearance']['mouth']);
        $this->assertSame($mouth->getId(), $data['races'][0]['appearance']['mouth'][0]['id']);
        $this->assertCount(1, $data['races'][0]['appearance']['nose']);
        $this->assertSame($nose->getId(), $data['races'][0]['appearance']['nose'][0]['id']);
        $this->assertCount(1, $data['races'][0]['appearance']['ears']);
        $this->assertSame($ears->getId(), $data['races'][0]['appearance']['ears'][0]['id']);

        $this->assertCount(1, $data['races'][1]['appearance']['eyes']);
        $this->assertCount(1, $data['races'][1]['appearance']['hair']);
        $this->assertCount(0, $data['races'][1]['appearance']['mouth']);
        $this->assertCount(0, $data['races'][1]['appearance']['nose']);
        $this->assertCount(3, $data['races'][1]['appearance']['ears']);
        $this->assertGreaterThanOrEqual($data['races'][1]['appearance']['ears'][0]['sortOrder'], $data['races'][1]['appearance']['ears'][1]['sortOrder']);
        $this->assertGreaterThanOrEqual($data['races'][1]['appearance']['ears'][1]['sortOrder'], $data['races'][1]['appearance']['ears'][2]['sortOrder']);

    }

    public function testRegisterOptionsHasOnlyGet(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/auth/register/options');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request('POST', '/api/auth/register/options');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);

        $client->request('DELETE', '/api/auth/register/options');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);

        $client->request('PATCH', '/api/auth/register/options');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);


    }
}
