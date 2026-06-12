<?php

namespace App\Entity\Item;

use App\Repository\Item\ItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ItemDefinition $definition;

    #[ORM\Column]
    private int $bonusDamage = 0;

    #[ORM\Column]
    private int $bonusCrit = 0;

    #[ORM\Column]
    private int $bonusHealth = 0;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDefinition(): ItemDefinition
    {
        return $this->definition;
    }

    public function setDefinition(ItemDefinition $definition): static
    {
        $this->definition = $definition;

        return $this;
    }

    public function getBonusDamage(): int
    {
        return $this->bonusDamage;
    }

    public function setBonusDamage(int $bonusDamage): static
    {
        $this->bonusDamage = $bonusDamage;

        return $this;
    }

    public function getBonusCrit(): int
    {
        return $this->bonusCrit;
    }

    public function setBonusCrit(int $bonusCrit): static
    {
        $this->bonusCrit = $bonusCrit;

        return $this;
    }

    public function getBonusHealth(): int
    {
        return $this->bonusHealth;
    }

    public function setBonusHealth(int $bonusHealth): static
    {
        $this->bonusHealth = $bonusHealth;

        return $this;
    }
}
