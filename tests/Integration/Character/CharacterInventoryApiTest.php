<?php

declare(strict_types=1);

namespace App\Tests\Integration\Character;

use App\Entity\Character\CharacterInventory;
use App\Entity\Item\InventoryContainerEnum;
use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemSlotEnum;
use App\Factory\CharacterFactory;
use App\Factory\CharacterInventoryFactory;
use App\Factory\ItemDefinitionFactory;
use App\Factory\ItemFactory;
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

    public function testPatchInventoryEquipsItem(): void
    {
        $inventory = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
            'position' => '2'
        ]);

        $character = CharacterFactory::createOne([
            'backpackCapacity' => 8
        ]);

        $character->addCharacterInventory($inventory);

        $client = static::createAuthenticatedClient($character);

        $client->request('GET', '/api/character/inventory/' . $inventory->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            '@type' => 'CharacterInventory',
            'container' => InventoryContainerEnum::Backpack->value,
            'position' => 2,
        ]);

        $client->request('PATCH', '/api/character/inventory/' . $inventory->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'container' => 'equipped',
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            '@type' => 'CharacterInventory',
            'container' => 'equipped',
            'position' => 0,
        ]);
    }

    public function testPatchReequipSameSlotItemsHotSwapsThem(): void
    {
        $character = CharacterFactory::createOne();

        $inventoryUnequipped = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
            'position' => '3',
            'item' => ItemFactory::createOne([
                'definition' => ItemDefinitionFactory::createOne([
                    'desiredSlot' => ItemSlotEnum::Helmet
                ])
            ]),
        ]);
        $inventoryEquipped = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Equipped,
            'item' => ItemFactory::createOne([
                'definition' => ItemDefinitionFactory::createOne([
                    'desiredSlot' => ItemSlotEnum::Helmet
                ])
            ]),
        ]);

        $character->addCharacterInventory($inventoryEquipped);
        $character->addCharacterInventory($inventoryUnequipped);

        $client = static::createAuthenticatedClient($character);

        $client->request('PATCH', '/api/character/inventory/' . $inventoryUnequipped->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'container' => 'equipped',
            ]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'container' => InventoryContainerEnum::Equipped->value,
        ]);

        $client->request('GET', '/api/character/inventory/' . $inventoryEquipped->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // New position of unequipped item is swapped with old position
        $this->assertJsonContains([
            'container' => InventoryContainerEnum::Backpack->value,
            'position' => 3,
        ]);
    }
    // TODO - testSellInventoryItemAddsGoldAndRemovesItem - Overit, ze POST /api/character/inventory/{id}/sell pricte postave goldy a smaze predmet
    // TODO - testUseElixirInventoryItemAppliesActiveElixir - Overit, ze POST /api/character/inventory/{id}/use spotrebuje elixir a aktivuje buff
    // TODO - testCannotAccessOrManipulateAnotherCharactersInventory - Overit autorizacni barieru: nelze manipulovat s inventarem cizi postavy (403/404)
}
