<?php

declare(strict_types=1);

namespace App\Tests\Integration\Shop;

use App\Entity\Item\InventoryContainerEnum;
use App\Entity\Item\ItemSlotEnum;
use App\Factory\CharacterFactory;
use App\Factory\CharacterInventoryFactory;
use App\Factory\ShopOfferFactory;
use App\Factory\ShopRotationFactory;
use App\Service\Shop\RotationGenerator;
use App\Tests\Integration\AbstractApiTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

class ShopOfferBuyApiTest extends AbstractApiTestCase
{
    // TODO - testBuyOfferSuccessfulDeductsGoldAndAddsItemToBackpack - Overit nakup: odecteni zlata, vznik Item v batohu a oznaceni nabidky jako koupene
    public function testBuyOfferSuccessfulDeductsGoldAndAddsItemToBackpack(): void
    {
        $character = CharacterFactory::createOne([
            'gold' => 100,
            'diamonds' => 100
        ]);
        $rotation = ShopRotationFactory::createOne(['character' => $character]);
        $offer = ShopOfferFactory::createOne(['rotation' => $rotation, 'goldPrice' => 90, 'diamondPrice' => 80]);

        $itemDamage = $offer->getBonusDamage() + $offer->getItemDefinition()->getBaseDamage();
        $itemHealth = $offer->getBonusHealth() + $offer->getItemDefinition()->getBaseHealth();
        $itemCrit = $offer->getBonusCrit() + $offer->getItemDefinition()->getBaseCrit();

        $client = static::createAuthenticatedClient($character);

        $client->request('POST', '/api/shop/offer/' . $offer->getId() , [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJsonContains([
            'name' => $offer->getItemDefinition()->getName(),
            'damage' => $itemDamage,
            'health' => $itemHealth,
            'crit' => $itemCrit,
            'requiredLevel' => $offer->getItemDefinition()->getRequiredLevel(),
            'slot' => $offer->getItemDefinition()->getDesiredSlot()->value,
        ]);


        $response = $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            'gold' => 10,
            'diamonds' => 20
        ]);

        $data = $response->toArray(false);
        $this->assertArrayHasKey('characterInventories', $data);
        $this->assertCount(1, $data['characterInventories']);

        $itemUri = $data['characterInventories'][0];

        $client->request('GET', $itemUri);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            'quantity' => 1,
            'container' => InventoryContainerEnum::Backpack->value,
            'item' => [
                'name' => $offer->getItemDefinition()->getName(),
                'damage' => $itemDamage,
                'health' => $itemHealth,
                'crit' => $itemCrit,
                'requiredLevel' => $offer->getItemDefinition()->getRequiredLevel(),
                'slot' => $offer->getItemDefinition()->getDesiredSlot()->value,
            ]
        ]);
    }

    public function testBuyOfferFailsWhenInsufficientFunds(): void
    {
        $character = CharacterFactory::createOne([
            'gold' => 1,
            'diamonds' => 1
        ]);
        $rotation = ShopRotationFactory::createOne(['character' => $character]);
        $offer = ShopOfferFactory::createOne(['rotation' => $rotation, 'goldPrice' => 100]);

        $client = static::createAuthenticatedClient($character);

        $client->request('POST', '/api/shop/offer/' . $offer->getId() , [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertJsonContains([
            'detail' => 'Not enough gold',
        ]);
    }

    public function testBuyOfferFailsWhenBackpackFull(): void
    {
        $character = CharacterFactory::createOne([
            'gold' => 10,
            'backpackCapacity' => 2,
        ]);

        $rotation = ShopRotationFactory::createOne(['character' => $character]);
        $offer = ShopOfferFactory::createOne(['rotation' => $rotation, 'goldPrice' => 1]);

        $characterInventorySlot1 = CharacterInventoryFactory::createOne(['container' => InventoryContainerEnum::Backpack, 'character' => $character]);
        $characterInventorySlot2 = CharacterInventoryFactory::createOne(['container' => InventoryContainerEnum::Backpack, 'character' => $character]);

        $character->addCharacterInventory($characterInventorySlot1);
        $character->addCharacterInventory($characterInventorySlot2);


        $client = static::createAuthenticatedClient($character);

        $client->request('POST', '/api/shop/offer/' . $offer->getId() , [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertJsonContains([
            'detail' => 'Not enough backpack space',
        ]);

    }
    public function testBuyOfferFailsWhenRotationExpired(): void
    {
        $character = CharacterFactory::createOne();
        $rotation = ShopRotationFactory::createOne([
            'character' => $character,
            'validFrom' => new DateTimeImmutable('yesterday - 2 days'),
            'validUntil' => new DateTimeImmutable('yesterday'),
            ]);
        $offer = ShopOfferFactory::createOne(['rotation' => $rotation]);

        $client = static::createAuthenticatedClient($character);

        $client->request('POST', '/api/shop/offer/' . $offer->getId() , [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertJsonContains([
            'detail' => 'Rotation not available',
        ]);
    }


    public function testBuyOfferFailsWhenOfferAlreadyBought(): void
    {
        $character = CharacterFactory::createOne();
        $rotation = ShopRotationFactory::createOne([ 'character' => $character]);
        $offer = ShopOfferFactory::createOne(['rotation' => $rotation]);

        $offerId = $offer->getId();

        $client = static::createAuthenticatedClient($character);

        $client->request('POST', '/api/shop/offer/' . $offerId , [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('POST', '/api/shop/offer/' . $offerId , [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->assertJsonContains([
            'detail' => 'Shop offer not found',
        ]);
    }

    public function testCannotBuyOfferFromAnotherCharactersRotation(): void
    {
        $character = CharacterFactory::createOne();
        $characterEnemy = CharacterFactory::createOne();

        $rotation = ShopRotationFactory::createOne([ 'character' => $characterEnemy]);
        $offer = ShopOfferFactory::createOne(['rotation' => $rotation]);

        $client = static::createAuthenticatedClient($character);

        $client->request('POST', '/api/shop/offer/' . $offer->getId() , [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testBuyOfferRequiresAuthentication(): void
    {
        $character = CharacterFactory::createOne();

        $rotation = ShopRotationFactory::createOne([ 'character' => $character]);
        $offer = ShopOfferFactory::createOne(['rotation' => $rotation]);

        $client = static::createClient();

        $client->request('POST', '/api/shop/offer/' . $offer->getId() , [
            'json' => []
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
