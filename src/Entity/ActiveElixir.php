<?php

namespace App\Entity;

use App\Entity\Character\Character;
use App\Entity\Item\ItemDefinition;
use App\Repository\ActiveElixirRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActiveElixirRepository::class)]
class ActiveElixir
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'activeElixirs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Character $character = null;

    #[ORM\ManyToOne(targetEntity: ItemDefinition::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?ItemDefinition $itemDefinition = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?DateTimeImmutable $expiresAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCharacter(): ?Character
    {
        return $this->character;
    }

    public function setCharacter(?Character $character): static
    {
        $this->character = $character;

        return $this;
    }

    public function getItemDefinition(): ?ItemDefinition
    {
        return $this->itemDefinition;
    }

    public function setItemDefinition(?ItemDefinition $itemDefinition): static
    {
        $this->itemDefinition = $itemDefinition;

        return $this;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}
