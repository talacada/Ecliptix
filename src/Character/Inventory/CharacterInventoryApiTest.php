<?php

declare(strict_types=1);

namespace App\Character\Inventory;

use App\Character\Character\CharacterFactory;
use App\Character\Elixir\ActiveElixirFactory;
use App\Item\Definition\ElixirDefinitionFactory;
use App\Item\Definition\ElixirTypeEnum;
use App\Item\Definition\ItemDefinitionFactory;
use App\Item\Definition\ItemSlotEnum;
use App\Item\Item\ItemFactory;
use App\Tests\Support\AbstractApiTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
                    ],
                ],
            ],
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetSingleInventorySlotReturnsItemDetails(): void
    {
        $item = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
        ]);

        $character = CharacterFactory::createOne();
        $character->addCharacterInventory($item);

        $client = static::createAuthenticatedClient($character);

        $client->request('GET', '/api/character/inventory/'.$item->getId());

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
        $character = CharacterFactory::createOne([
            'backpackCapacity' => 8,
        ]);

        $inventory = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
            'position' => '2',
            'character' => $character,
        ]);

        $client = static::createAuthenticatedClient($character);

        $client->request('GET', '/api/character/inventory/'.$inventory->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            '@type' => 'CharacterInventory',
            'container' => InventoryContainerEnum::Backpack->value,
            'position' => 2,
        ]);

        $client->request('PATCH', '/api/character/inventory/'.$inventory->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'container' => 'equipped',
            ],
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
                    'desiredSlot' => ItemSlotEnum::Helmet,
                ]),
            ]),
            'character' => $character,
        ]);
        $inventoryEquipped = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Equipped,
            'item' => ItemFactory::createOne([
                'definition' => ItemDefinitionFactory::createOne([
                    'desiredSlot' => ItemSlotEnum::Helmet,
                ]),
            ]),
            'character' => $character,
        ]);

        $client = static::createAuthenticatedClient($character);

        $client->request('PATCH', '/api/character/inventory/'.$inventoryUnequipped->getId(), [
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => [
                'container' => 'equipped',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'container' => InventoryContainerEnum::Equipped->value,
        ]);

        $client->request('GET', '/api/character/inventory/'.$inventoryEquipped->getId());

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
            'gold' => 10,
            'diamonds' => 5,
        ]);

        $inventoryUnequipped = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
            'position' => '3',
            'item' => ItemFactory::createOne([
                'definition' => ItemDefinitionFactory::createOne([
                    'desiredSlot' => ItemSlotEnum::Helmet,
                ]),
            ]),
            'goldSellPrice' => 132,
        ]);

        $inventoryEquipped = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Equipped,
            'item' => ItemFactory::createOne([
                'definition' => ItemDefinitionFactory::createOne([
                    'desiredSlot' => ItemSlotEnum::Helmet,
                ]),
            ]),
            'goldSellPrice' => 1456,
        ]);

        $elixir = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
            'quantity' => 3,
            'item' => ItemFactory::createOne([
                'definition' => ElixirDefinitionFactory::createOne(),
            ]),
            'goldSellPrice' => 452,
            'diamondSellPrice' => 10,
        ]);

        $character->addCharacterInventory($inventoryEquipped);
        $character->addCharacterInventory($inventoryUnequipped);
        $character->addCharacterInventory($elixir);

        $client = static::createAuthenticatedClient($character);

        // SELL 1
        $client->request('POST', '/api/character/inventory/'.$inventoryEquipped->getId().'/sell');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'gold' => intval(10 + $inventoryEquipped->getGoldSellPrice()),
        ]);
        $data = $response->toArray(false);
        $this->assertCount(2, $data['characterInventories']);
        $gold = $data['gold'];

        // SELL 2
        $client->request('POST', '/api/character/inventory/'.$inventoryUnequipped->getId().'/sell');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'gold' => intval($gold + $inventoryUnequipped->getGoldSellPrice()),
        ]);
        $data = $response->toArray(false);
        $this->assertCount(1, $data['characterInventories']);
        $gold = $data['gold'];
        $diamonds = $data['diamonds'];

        // SELL 3
        $client->request('POST', '/api/character/inventory/'.$elixir->getId().'/sell');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $client->request('GET', '/api/character/inventory/'.$elixir->getId());

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'quantity' => 2,
        ]);

        $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'gold' => $gold + 150,
            'diamonds' => $diamonds + 3,
        ]);

        CharacterInventoryFactory::assert()->exists([
            'id' => $elixir->getId(),
            'quantity' => 2,
            'goldSellPrice' => 302,
            'diamondSellPrice' => 7,
        ]);
    }

    public function testUseElixirInventoryItemAppliesActiveElixir(): void
    {
        $character = CharacterFactory::createOne();

        $definition = ElixirDefinitionFactory::createOne();

        $elixir = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
            'quantity' => 3,
            'item' => ItemFactory::createOne([
                'definition' => $definition,
            ]),
        ]);

        $character->addCharacterInventory($elixir);

        $client = static::createAuthenticatedClient($character);

        $client->request('POST', '/api/character/inventory/'.$elixir->getId().'/use');

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJsonContains([
            '@type' => 'ActiveElixir',
        ]);

        $response = $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            'activeElixirs' => [
                [
                    '@type' => 'ActiveElixir',
                    'name' => $elixir->getItem()->getDefinition()->getName(),
                    'percentageBonus' => $definition->getPercentageBonus(),
                ],
            ],
        ]);
    }

    public function testCannotAccessOrManipulateAnotherCharactersInventory(): void
    {
        $character = CharacterFactory::createOne();
        $charater2 = CharacterFactory::createOne();

        $elixir = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
            'quantity' => 3,
            'item' => ItemFactory::createOne([
                'definition' => ElixirDefinitionFactory::createOne(),
            ]),
        ]);

        $character->addCharacterInventory($elixir);

        $client = static::createClient();

        $client->request('POST', '/api/character/inventory/'.$elixir->getId().'/sell');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        $client = static::createAuthenticatedClient($charater2);

        $client->request('POST', '/api/character/inventory/'.$elixir->getId().'/sell');
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testCantUseNonElixirItems(): void
    {
        $character = CharacterFactory::createOne();

        $item = CharacterInventoryFactory::createOne([
            'container' => InventoryContainerEnum::Backpack,
            'quantity' => 3,
            'item' => ItemFactory::createOne([
                'definition' => ItemDefinitionFactory::createOne(),
            ]),
            'character' => $character,
        ]);

        $client = static::createAuthenticatedClient($character);

        $client->request('POST', '/api/character/inventory/'.$item->getId().'/use');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $this->assertJsonContains([
            'detail' => 'This item cannot be activated',
        ]);

        $client->request('GET', '/api/character');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJsonContains([
            'characterInventories' => [
                '/api/character/inventory/'.$item->getId(),
            ],
        ]);
    }

    public function testUseElixirFromStackReducesSellPriceForRemainingUnit(): void
    {
        $character = CharacterFactory::createOne([
            'gold' => 100,
            'diamonds' => 10,
        ]);

        $elixir = CharacterInventoryFactory::createOne([
            'character' => $character,
            'container' => InventoryContainerEnum::Backpack,
            'quantity' => 2,
            'item' => ItemFactory::createOne([
                'definition' => ElixirDefinitionFactory::createOne(),
            ]),
            'goldSellPrice' => 300,
            'diamondSellPrice' => 20,
        ]);

        $client = static::createAuthenticatedClient($character);

        $client->request('POST', '/api/character/inventory/'.$elixir->getId().'/use');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        CharacterInventoryFactory::assert()->exists([
            'id' => $elixir->getId(),
            'quantity' => 1,
            'goldSellPrice' => 150,
            'diamondSellPrice' => 10,
        ]);

        $client->request('POST', '/api/character/inventory/'.$elixir->getId().'/sell');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $response = $client->request('GET', '/api/character');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'gold' => 250,
            'diamonds' => 20,
        ]);

        $data = $response->toArray(false);
        $this->assertCount(0, $data['characterInventories']);
        CharacterInventoryFactory::assert()->count(0, ['character' => $character]);
    }

    public function testUseElixirMatchesByEffectAndRespectsMaxSlots(): void
    {
        $character = CharacterFactory::createOne([
            'backpackCapacity' => 6,
        ]);

        $damage10Short = ElixirDefinitionFactory::createOne([
            'elixirType' => ElixirTypeEnum::Damage,
            'percentageBonus' => 10,
            'durationSeconds' => 3600,
        ]);
        $damage10Long = ElixirDefinitionFactory::createOne([
            'elixirType' => ElixirTypeEnum::Damage,
            'percentageBonus' => 10,
            'durationSeconds' => 259200,
        ]);
        $damage20 = ElixirDefinitionFactory::createOne([
            'elixirType' => ElixirTypeEnum::Damage,
            'percentageBonus' => 20,
            'durationSeconds' => 3600,
        ]);
        $health10 = ElixirDefinitionFactory::createOne([
            'elixirType' => ElixirTypeEnum::Health,
            'percentageBonus' => 10,
            'durationSeconds' => 3600,
        ]);
        $crit25 = ElixirDefinitionFactory::createOne([
            'elixirType' => ElixirTypeEnum::Critical,
            'percentageBonus' => 25,
            'durationSeconds' => 3600,
        ]);

        $invDamage10First = CharacterInventoryFactory::createOne([
            'character' => $character,
            'container' => InventoryContainerEnum::Backpack,
            'position' => 1,
            'quantity' => 1,
            'item' => ItemFactory::createOne(['definition' => $damage10Short]),
        ]);
        $invDamage10Second = CharacterInventoryFactory::createOne([
            'character' => $character,
            'container' => InventoryContainerEnum::Backpack,
            'position' => 2,
            'quantity' => 2,
            'item' => ItemFactory::createOne(['definition' => $damage10Long]),
        ]);
        $invDamage20 = CharacterInventoryFactory::createOne([
            'character' => $character,
            'container' => InventoryContainerEnum::Backpack,
            'position' => 3,
            'quantity' => 1,
            'item' => ItemFactory::createOne(['definition' => $damage20]),
        ]);
        $invHealth10 = CharacterInventoryFactory::createOne([
            'character' => $character,
            'container' => InventoryContainerEnum::Backpack,
            'position' => 4,
            'quantity' => 1,
            'item' => ItemFactory::createOne(['definition' => $health10]),
        ]);
        $invCrit25 = CharacterInventoryFactory::createOne([
            'character' => $character,
            'container' => InventoryContainerEnum::Backpack,
            'position' => 5,
            'quantity' => 1,
            'item' => ItemFactory::createOne(['definition' => $crit25]),
        ]);

        $client = static::createAuthenticatedClient($character);

        // 1. Activate first Damage +10% elixir
        $response = $client->request('POST', '/api/character/inventory/'.$invDamage10First->getId().'/use');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $firstUseData = $response->toArray();
        $firstElixirUri = $firstUseData['@id'];

        $firstData = $client->request('GET', $firstElixirUri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $initialRemaining = $firstData['remainingSeconds'];
        $this->assertGreaterThanOrEqual(3550, $initialRemaining);
        $this->assertLessThanOrEqual(3600, $initialRemaining);
        ActiveElixirFactory::assert()->count(1, ['character' => $character]);

        // 2. Different ElixirDefinition with same elixirType + percentageBonus extends expiration without adding a slot
        $response = $client->request('POST', '/api/character/inventory/'.$invDamage10Second->getId().'/use');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $extendedUseData = $response->toArray();
        $this->assertSame($firstElixirUri, $extendedUseData['@id']);

        $extendedData = $client->request('GET', $firstElixirUri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertGreaterThanOrEqual($initialRemaining + 259150, $extendedData['remainingSeconds']);
        $this->assertLessThanOrEqual($initialRemaining + 259200, $extendedData['remainingSeconds']);
        ActiveElixirFactory::assert()->count(1, ['character' => $character]);

        // 3. Same elixirType with different percentageBonus creates a second active slot
        $response = $client->request('POST', '/api/character/inventory/'.$invDamage20->getId().'/use');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $damage20Uri = $response->toArray()['@id'];
        $this->assertNotSame($firstElixirUri, $damage20Uri);

        $damage20Data = $client->request('GET', $damage20Uri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSame(20, $damage20Data['percentageBonus']);
        ActiveElixirFactory::assert()->count(2, ['character' => $character]);

        // 4. Different elixirType with same percentageBonus creates a third active slot (reaches limit of 3)
        $response = $client->request('POST', '/api/character/inventory/'.$invHealth10->getId().'/use');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $health10Uri = $response->toArray()['@id'];
        $this->assertNotSame($firstElixirUri, $health10Uri);
        $this->assertNotSame($damage20Uri, $health10Uri);

        $health10Data = $client->request('GET', $health10Uri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSame(10, $health10Data['percentageBonus']);
        ActiveElixirFactory::assert()->count(3, ['character' => $character]);

        // 5. At limit 3, an elixir matching an existing active effect still extends its slot
        $response = $client->request('POST', '/api/character/inventory/'.$invDamage10Second->getId().'/use');
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $reextendedUseData = $response->toArray();
        $this->assertSame($firstElixirUri, $reextendedUseData['@id']);

        $reextendedData = $client->request('GET', $firstElixirUri)->toArray();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertGreaterThanOrEqual($extendedData['remainingSeconds'] + 259150, $reextendedData['remainingSeconds']);
        $this->assertLessThanOrEqual($extendedData['remainingSeconds'] + 259200, $reextendedData['remainingSeconds']);
        ActiveElixirFactory::assert()->count(3, ['character' => $character]);

        // 6. At limit 3, a 4th distinct effect is rejected and remains in inventory
        $client->request('POST', '/api/character/inventory/'.$invCrit25->getId().'/use');
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertJsonContains([
            'detail' => 'Cant have more than 3 elixirs activated',
        ]);
        ActiveElixirFactory::assert()->count(3, ['character' => $character]);
        CharacterInventoryFactory::assert()->exists([
            'id' => $invCrit25->getId(),
            'quantity' => 1,
        ]);

        $characterResponse = $client->request('GET', '/api/character');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $characterData = $characterResponse->toArray(false);
        $this->assertCount(3, $characterData['activeElixirs']);
    }
}
