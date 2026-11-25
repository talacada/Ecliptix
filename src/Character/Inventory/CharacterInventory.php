<?php

declare(strict_types=1);

namespace App\Character\Inventory;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Character\Character\Character;
use App\Item\Item\Item;
use App\Item\Item\ItemViewDTO;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: 'character/inventory',
            provider: CharacterInventoryProvider::class,
        ),
        new Get(
            uriTemplate: 'character/inventory/{inventoryId}',
            uriVariables: [
                'inventoryId' => new Link(
                    fromClass: CharacterInventory::class,
                    identifiers: ['id'],
                ),
            ],
            provider: CharacterInventoryProvider::class,
        ),
        new Post(
            uriTemplate: 'character/inventory/{inventoryId}/sell',
            // This is paring unknown uri variable to specific class and property
            uriVariables: [
                'inventoryId' => new Link(
                    fromClass: CharacterInventory::class,
                    identifiers: ['id'],
                ),
            ],
            deserialize: false,
            provider: CharacterInventoryProvider::class,
            processor: CharacterInventorySellProcessor::class,
        ),
        new Patch(
            uriTemplate: 'character/inventory/{inventoryId}',
            uriVariables: [
                'inventoryId' => new Link(
                    fromClass: CharacterInventory::class,
                    identifiers: ['id'],
                ),
            ],
            denormalizationContext: ['groups' => [self::WRITE_GROUP]],
            provider: CharacterInventoryProvider::class,
            processor: CharacterInventoryEditProcessor::class,
        ),
        new Post(
            uriTemplate: 'character/inventory/{inventoryId}/use',
            uriVariables: [
                'inventoryId' => new Link(
                    fromClass: CharacterInventory::class,
                    identifiers: ['id'],
                ),
            ],
            deserialize: false,
            provider: CharacterInventoryProvider::class,
            processor: CharacterInventoryUseProcessor::class,
        ),
    ],
    normalizationContext: ['groups' => [self::READ_GROUP, ItemViewDTO::READ_GROUP]],
    security: 'is_granted("ROLE_USER")',
)]
#[ORM\Entity(repositoryClass: CharacterInventoryRepository::class)]
class CharacterInventory
{
    public const READ_GROUP = 'inventory:read';
    public const WRITE_GROUP = 'inventory:write';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'characterInventories')]
    #[ORM\JoinColumn(nullable: false)]
    private Character $character;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Item $item;

    #[ORM\Column(nullable: false)]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    private InventoryContainerEnum $container;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $quantity = 1;

    #[ORM\Column]
    #[Groups([self::READ_GROUP, self::WRITE_GROUP])]
    private int $position = -1;

    #[Groups([self::READ_GROUP])]
    #[SerializedName('item')]
    private ?ItemViewDTO $itemViewDTO = null;

    #[ORM\Column]
    private int $goldSellPrice = 0;

    #[ORM\Column]
    private int $diamondSellPrice = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): Character
    {
        return $this->character;
    }

    public function getItemViewDTO(): ?ItemViewDTO
    {
        return $this->itemViewDTO;
    }

    public function setItemViewDTO(?ItemViewDTO $itemViewDTO): void
    {
        $this->itemViewDTO = $itemViewDTO;
    }

    public function setCharacter(Character $character): static
    {
        $this->character = $character;

        return $this;
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function setItem(Item $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function getContainer(): InventoryContainerEnum
    {
        return $this->container;
    }

    public function setContainer(InventoryContainerEnum $container): void
    {
        $this->container = $container;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function addQuantity(int $quantity): void
    {
        $this->quantity += $quantity;
    }

    public function removeQuantity(int $quantity): void
    {
        $this->quantity -= $quantity;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getGoldSellPrice(): int
    {
        return $this->goldSellPrice;
    }

    public function setGoldSellPrice(int $goldSellPrice): void
    {
        $this->goldSellPrice = $goldSellPrice;
    }

    public function removeGoldSellPrice(int $gold): void
    {
        $this->goldSellPrice -= $gold;
    }

    public function removeGoldSellPriceByUnits(int $units): int
    {
        $removing = $this->quantity === $units ? $this->goldSellPrice : intdiv($this->goldSellPrice, $this->quantity) * $units;
        $this->goldSellPrice -= $removing;

        return $removing;
    }

    public function addGoldSellPrice(int $goldPrice): void
    {
        $this->goldSellPrice += $goldPrice;
    }

    public function getDiamondSellPrice(): int
    {
        return $this->diamondSellPrice;
    }

    public function setDiamondSellPrice(int $diamondSellPrice): void
    {
        $this->diamondSellPrice = $diamondSellPrice;
    }

    public function addDiamondSellPrice(int $diamondPaid): void
    {
        $this->diamondSellPrice += $diamondPaid;
    }

    public function removeDiamondSellPriceByUnits(int $units): int
    {
        $removing = $this->quantity === $units ? $this->diamondSellPrice : intdiv($this->diamondSellPrice, $this->quantity) * $units;
        $this->diamondSellPrice -= $removing;

        return $removing;
    }
}
