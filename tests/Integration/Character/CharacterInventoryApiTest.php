<?php

declare(strict_types=1);

namespace App\Tests\Integration\Character;

use App\Tests\Integration\AbstractApiTestCase;

class CharacterInventoryApiTest extends AbstractApiTestCase
{

    // TODO - testGetInventoryRequiresAuthentication - Overit, ze GET /api/character/inventory bez tokenu vrati 401 Unauthorized
    // TODO - testGetInventoryReturnsAllUnequippedAndEquippedItems - Overit, ze GET /api/character/inventory vrati obsah batohu i slotu postavy
    // TODO - testGetSingleInventorySlotReturnsItemDetails - Overit GET /api/character/inventory/{id} pro konkretni slot
    // TODO - testPatchInventoryEquipsItemToValidSlot - Overit presun predmetu z batohu do odpovidajiciho slotu vybavy
    // TODO - testPatchInventoryFailsWhenEquippingWrongSlot - Overit, ze nelze nasadit helmu do slotu pro zbran (400/422)
    // TODO - testSellInventoryItemAddsGoldAndRemovesItem - Overit, ze POST /api/character/inventory/{id}/sell pricte postave goldy a smaze predmet
    // TODO - testUseElixirInventoryItemAppliesActiveElixir - Overit, ze POST /api/character/inventory/{id}/use spotrebuje elixir a aktivuje buff
    // TODO - testCannotAccessOrManipulateAnotherCharactersInventory - Overit autorizacni barieru: nelze manipulovat s inventarem cizi postavy (403/404)
}
