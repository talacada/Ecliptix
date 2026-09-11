<?php

declare(strict_types=1);

namespace App\Tests\Integration\Shop;

use App\Tests\Integration\AbstractApiTestCase;

class ShopOfferBuyApiTest extends AbstractApiTestCase
{

    // TODO - testBuyOfferSuccessfulDeductsGoldAndAddsItemToBackpack - Overit nakup: odecteni zlata, vznik Item v batohu a oznaceni nabidky jako koupene
    // TODO - testBuyOfferFailsWhenInsufficientFunds - Overit selhani nakupu pri nedostatku penez (400/422)
    // TODO - testBuyOfferFailsWhenBackpackFull - Overit selhani nakupu, pokud postava nema misto v batohu
    // TODO - testBuyOfferFailsWhenOfferExpiredOrAlreadyBought - Overit, ze nelze koupit jiz koupenou nebo expirovanou nabidku
    // TODO - testCannotBuyOfferFromAnotherCharactersRotation - Overit bezpecnostni barieru: nelze koupit nabidku z cizi rotace (403/404)
    // TODO - testBuyOfferRequiresAuthentication - Overit 401 Unauthorized bez prihlaseni
}
