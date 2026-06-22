<?php

namespace App\Entity\Character;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiResource\Item\ItemViewDTO;
use App\Entity\Item\Item;
use App\Repository\Character\CharacterInventoryRepository;
use App\State\Processor\Character\Inventory\CharacterInventoryChangeOrderProcessor;
use App\State\Processor\Character\Inventory\CharacterInventoryEquipProcessor;
use App\State\Processor\Character\Inventory\CharacterInventorySellProcessor;
use App\State\Provider\Character\Inventory\CharacterInventoryProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: 'character/inventory',
            provider: CharacterInventoryProvider::class
        ),
        new Post(
            uriTemplate: 'character/inventory/{inventoryId}/equip',
            //This is paring unknown uri variable to specific class and property
            uriVariables: [
                'inventoryId' => new Link(
                    fromClass: CharacterInventory::class,
                    identifiers: ['id']
                ),
            ],
            deserialize: false,
            provider: CharacterInventoryProvider::class,
            processor: CharacterInventoryEquipProcessor::class
        ),
        new Post (
            uriTemplate: 'character/inventory/{inventoryId}/sell',
            uriVariables: [
                'inventoryId' => new Link(
                    fromClass: CharacterInventory::class,
                    identifiers: ['id']
                )
            ],
            deserialize: false,
            provider: CharacterInventoryProvider::class,
            processor: CharacterInventorySellProcessor::class
        ),
        new Patch(
            //With specific name id=id property link is not needed, but its less readable sometimes
            uriTemplate: 'character/inventory/{id}/reposition',
            provider: CharacterInventoryProvider::class,
            processor: CharacterInventoryChangeOrderProcessor::class
        )
    ],
    normalizationContext: ['groups' => [self::READ_GROUP, ItemViewDTO::READ_GROUP]],
    security: 'is_granted("ROLE_USER")',
)]
#[ORM\Entity(repositoryClass: CharacterInventoryRepository::class)]
class CharacterInventory
{
    public const READ_GROUP = 'character_inventory:read';
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

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private bool $equipped = false;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $quantity = 1;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $position = 0;

    #[Groups([self::READ_GROUP])]
    #[SerializedName('item')]
    private ?ItemViewDTO $itemViewDTO = null;

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

    public function setCharacter(?Character $character): static
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

    public function isEquipped(): bool
    {
        return $this->equipped;
    }

    public function setEquipped(bool $equipped): static
    {
        $this->equipped = $equipped;

        return $this;
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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }


}
