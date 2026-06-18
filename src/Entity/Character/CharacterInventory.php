<?php

namespace App\Entity\Character;

use ApiPlatform\Metadata\ApiResource;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiResource\Item\ItemViewDTO;
use App\Entity\Item\Item;
use App\Repository\Character\CharacterInventoryRepository;
use App\State\Provider\Character\CharacterInventoryProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: 'character/inventory',
            provider: CharacterInventoryProvider::class
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
    private ?Item $item = null;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private bool $equipped = false;

    #[ORM\Column]
    #[Groups([self::READ_GROUP])]
    private int $quantity = 1;

    #[Groups([self::READ_GROUP])]
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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): static
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
}
