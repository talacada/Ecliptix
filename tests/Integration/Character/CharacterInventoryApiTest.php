<?php

declare(strict_types=1);

namespace App\Tests\Integration\Character;

use App\Entity\Item\InventoryContainerEnum;
use App\Factory\CharacterFactory;
use App\Factory\CharacterInventoryFactory;
use App\Tests\Integration\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class CharacterInventoryApiTest extends AbstractApiTestCase
{
    public function testGetInventoryRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/character/inventory');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
    // TODO - testGetInventoryReturnsAllUnequippedAndEquippedItems - Overit, ze GET /api/character/inventory vrati obsah batohu i slotu postavy
    public function testGetInventoryReturnsAllUnequippedAndEquippedItems(): void
    {
        $equippedItem = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Equipped,
        ]);
        $inventoryItem = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
        ]);

        $character = CharacterFactory::createOne();
        $character->addCharacterInventory($equippedItem);
        $character->addCharacterInventory($inventoryItem);

        $client = static::createAuthenticatedClient($character);

        $response = $client->request('GET', '/api/character/inventory');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $response->toArray(false);
        //TODO continue here
    }
    // TODO - testGetSingleInventorySlotReturnsItemDetails - Overit GET /api/character/inventory/{id} pro konkretni slot
    // TODO - testPatchInventoryEquipsItemToValidSlot - Overit presun predmetu z batohu do odpovidajiciho slotu vybavy
    // TODO - testPatchInventoryFailsWhenEquippingWrongSlot - Overit, ze nelze nasadit helmu do slotu pro zbran (400/422)
    // TODO - testSellInventoryItemAddsGoldAndRemovesItem - Overit, ze POST /api/character/inventory/{id}/sell pricte postave goldy a smaze predmet
    // TODO - testUseElixirInventoryItemAppliesActiveElixir - Overit, ze POST /api/character/inventory/{id}/use spotrebuje elixir a aktivuje buff
    // TODO - testCannotAccessOrManipulateAnotherCharactersInventory - Overit autorizacni barieru: nelze manipulovat s inventarem cizi postavy (403/404)
}
