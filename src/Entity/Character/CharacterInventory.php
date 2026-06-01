<?php

namespace App\Entity\Character;

use ApiPlatform\Metadata\ApiResource;

use App\Entity\Item\Item;
use App\Repository\Character\CharacterInventoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CharacterInventoryRepository::class)]
class CharacterInventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'characterInventories')]
    #[ORM\JoinColumn(nullable: false)]
    private Character $character;

    #[ORM\OneToOne]
    private ?Item $item = null;

    #[ORM\Column]
    private bool $equipped = false;

    #[ORM\Column]
    private int $quantity = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): Character
    {
        return $this->character;
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
