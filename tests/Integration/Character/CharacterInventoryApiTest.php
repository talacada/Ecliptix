<?php

declare(strict_types=1);

namespace App\Tests\Integration\Character;

use App\Config\ItemConfig;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\InventoryContainerEnum;
use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemSlotEnum;
use App\Factory\CharacterFactory;
use App\Factory\CharacterInventoryFactory;
use App\Factory\ElixirDefinitionFactory;
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
    public function testSellInventoryItemAddsGoldAndRemovesItem(): void
    {
        $character = CharacterFactory::createOne([
            'gold' => 10
        ]);

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

        $elixir = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
            'quantity' => 3,
            'item' => ItemFactory::createOne([
                'definition' => ElixirDefinitionFactory::createOne()
            ]),
        ]);

        $character->addCharacterInventory($inventoryEquipped);
        $character->addCharacterInventory($inventoryUnequipped);
        $character->addCharacterInventory($elixir);

        $client = static::createAuthenticatedClient($character);

        //SELL 1
        $client->request('POST', '/api/character/inventory/' . $inventoryEquipped->getId() . '/sell');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'gold' => intval(10 + $inventoryEquipped->getItem()->getDefinition()->getBaseGoldPrice() * ItemConfig::SELL_PRICE_MULTIPLIER)
        ]);
        $data = $response->toArray(false);
        $this->assertCount(2, $data['characterInventories']);
        $gold = $data['gold'];

        //SELL 2
        $client->request('POST', '/api/character/inventory/' . $inventoryUnequipped->getId() . '/sell');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'gold' => intval($gold + $inventoryUnequipped->getItem()->getDefinition()->getBaseGoldPrice() * ItemConfig::SELL_PRICE_MULTIPLIER)
        ]);
        $data = $response->toArray(false);
        $this->assertCount(1, $data['characterInventories']);
        $gold = $data['gold'];

        //SELL 3
        $client->request('POST', '/api/character/inventory/' . $elixir->getId() . '/sell');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('GET', '/api/character/inventory/' . $elixir->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'quantity' => 2
        ]);
    }

    // TODO - testUseElixirInventoryItemAppliesActiveElixir - Overit, ze POST /api/character/inventory/{id}/use spotrebuje elixir a aktivuje buff
    // TODO - testCannotAccessOrManipulateAnotherCharactersInventory - Overit autorizacni barieru: nelze manipulovat s inventarem cizi postavy (403/404)
}
