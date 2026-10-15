<?php

declare(strict_types=1);

namespace App\Tests\Integration\Dictionary;

use App\Entity\Appearance\AppearanceTypeEnum;
use App\Entity\AppearanceOption;
use App\Factory\AppearanceOptionFactory;
use App\Factory\RaceFactory;
use App\Tests\Integration\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class AppearanceOptionApiTest extends AbstractApiTestCase
{

    // TODO - testGetAppearanceOptionsCollection - Overit, ze GET /api/appearance_options vrati vsechny moznosti vzhledu
    public function testGetAppearanceOptionsCollection()
    {
        $raceOne = RaceFactory::createOne();
        $raceTwo = RaceFactory::createOne();
        $raceThree = RaceFactory::createOne();

        $appearanceOneThree = AppearanceOptionFactory::createOne([
            "race" => $raceOne,
            "type" => AppearanceTypeEnum::hair,
            "sort_order" => 3,
        ]);
        $appearanceOneOne = AppearanceOptionFactory::createOne([
            "race" => $raceOne,
            "type" => AppearanceTypeEnum::hair,
            "sort_order" => 1,
        ]);
        $appearanceOneTwo = AppearanceOptionFactory::createOne([
            "race" => $raceOne,
            "type" => AppearanceTypeEnum::hair,
            "sort_order" => 2,
        ]);
        $appearanceTwo = AppearanceOptionFactory::createOne([
            "race" => $raceTwo,
            'type' => AppearanceTypeEnum::mouth,
        ]);
        $appearanceThree = AppearanceOptionFactory::createMany(7, [
            "race" => $raceThree,
            'type' => AppearanceTypeEnum::eyes
        ]);

        $client = self::createClient();

        $response = $client->request('GET', '/api/auth/register/options');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);
        $this->assertArrayHasKey("races", $data);
        $this->assertCount(3, $data['races']);

        $this->assertSame($raceOne->getName(), $data['races'][0]['name']);
        $this->assertCount(3, $data['races'][0]['appearance']['hair']);
        $this->assertSame($appearanceOneThree->getLabel(), $data['races'][0]['appearance']['hair'][2]['label']);
        $this->assertSame($appearanceOneThree->getSortOrder(), $data['races'][0]['appearance']['hair'][2]['sortOrder']);
        $this->assertSame($appearanceOneTwo->getLabel(), $data['races'][0]['appearance']['hair'][1]['label']);
        $this->assertSame($appearanceOneOne->getLabel(), $data['races'][0]['appearance']['hair'][0]['label']);


        $this->assertSame($raceTwo->getName(), $data['races'][1]['name']);
        $this->assertCount(1, $data['races'][1]['appearance']['mouth']);

        $this->assertSame($raceThree->getName(), $data['races'][2]['name']);
        $this->assertCount(7, $data['races'][2]['appearance']['eyes']);
    }

    // TODO - testGetSingleAppearanceOption - Overit, ze GET /api/appearance_options/{id} vrati konkretni detail moznosti vzhledu
    public function testGetAppearanceOptionsItem()
    {

    }
}
