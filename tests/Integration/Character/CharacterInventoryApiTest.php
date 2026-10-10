<?php

declare(strict_types=1);

namespace App\Tests\Integration\Character;

use App\Entity\Character\CharacterInventory;
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

        $client->request('GET', '/api/character/inventory');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);


        $this->assertJsonContains([
            'totalItems' => 2,
            'member' => [
                [
                    'container' => InventoryContainerEnum::Equipped->value,
                    'item' => [
                        'name' => $equippedItem->getItem()->getDefinition()->getName(),
                    ],
                ],
                [
                    'container' => InventoryContainerEnum::Backpack->value,
                    'item' => [
                        'name' => $inventoryItem->getItem()->getDefinition()->getName(),
                    ]
                ],
            ],
        ]);
    }

    public function testGetSingleInventorySlotReturnsItemDetails(): void
    {
        $item = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
        ]);

        $character = CharacterFactory::createOne();
        $character->addCharacterInventory($item);

        $client = static::createAuthenticatedClient($character);

        $client->request('GET', '/api/character/inventory/' . $item->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            '@type' => 'CharacterInventory',
            'item' => [
                'name' => $item->getItem()->getDefinition()->getName(),
            ],
        ]);

    }

    public function testPatchInventoryEquipsItemToValidSlot(): void
    {
        $item = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
        ]);

        $character = CharacterFactory::createOne();
        $character->addCharacterInventory($item);

        $client = static::createAuthenticatedClient($character);

        $client->request('PATCH', '/api/character/inventory/' . $item->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'equipped' => true
            ]
        ]);

        //TODO proccesor is broken i think
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
    // TODO - testPatchInventoryEquipsItemToValidSlot - Overit presun predmetu z batohu do odpovidajiciho slotu vybavy
    // TODO - testPatchInventoryFailsWhenEquippingWrongSlot - Overit, ze nelze nasadit helmu do slotu pro zbran (400/422)
    // TODO - testSellInventoryItemAddsGoldAndRemovesItem - Overit, ze POST /api/character/inventory/{id}/sell pricte postave goldy a smaze predmet
    // TODO - testUseElixirInventoryItemAppliesActiveElixir - Overit, ze POST /api/character/inventory/{id}/use spotrebuje elixir a aktivuje buff
    // TODO - testCannotAccessOrManipulateAnotherCharactersInventory - Overit autorizacni barieru: nelze manipulovat s inventarem cizi postavy (403/404)
}
