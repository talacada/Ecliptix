<?php

declare(strict_types=1);

namespace App\Tests\Integration\Shop;

use App\Factory\CharacterFactory;
use App\Factory\ShopOfferFactory;
use App\Factory\ShopRotationFactory;
use App\Service\Shop\RotationGenerator;
use App\Tests\Integration\AbstractApiTestCase;

class ShopOfferBuyApiTest extends AbstractApiTestCase
{
    // TODO - testBuyOfferSuccessfulDeductsGoldAndAddsItemToBackpack - Overit nakup: odecteni zlata, vznik Item v batohu a oznaceni nabidky jako koupene
    public function testBuyOfferSuccessfulDeductsGoldAndAddsItemToBackpack(): void
    {

    }
    // TODO - testBuyOfferFailsWhenInsufficientFunds - Overit selhani nakupu pri nedostatku penez (400/422)
    public function testBuyOfferFailsWhenInsufficientFunds(): void
    {
        $character = CharacterFactory::createOne();
        $rotation = ShopRotationFactory::createOne();
        $offer = ShopOfferFactory::createOne(['rotation' => $rotation]);


        $client = static::createAuthenticatedClient($character);



    }
    // TODO - testBuyOfferFailsWhenBackpackFull - Overit selhani nakupu, pokud postava nema misto v batohu
    public function testBuyOfferFailsWhenBackpackFull(): void
    {

    }
    // TODO - testBuyOfferFailsWhenOfferExpiredOrAlreadyBought - Overit, ze nelze koupit jiz koupenou nebo expirovanou nabidku
    public function testBuyOfferFailsWhenOfferExpiredOrAlreadyBought(): void
    {

    }
    // TODO - testCannotBuyOfferFromAnotherCharactersRotation - Overit bezpecnostni barieru: nelze koupit nabidku z cizi rotace (403/404)
    public function testCannotBuyOfferFromAnotherCharactersRotation(): void
    {

    }
    // TODO - testBuyOfferRequiresAuthentication - Overit 401 Unauthorized bez prihlaseni
    public function testBuyOfferRequiresAuthentication(): void
    {

    }
}
