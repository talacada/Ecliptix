<?php

namespace App\Entity;

use App\Entity\Item\AppearanceTypeEnum;
use App\Entity\Item\InventoryContainerEnum;
use App\Repository\AppearanceOptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppearanceOptionRepository::class)]
class AppearanceOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Race $race = null;

    #[ORM\Column(enumType: InventoryContainerEnum::class)]
    private ?AppearanceTypeEnum $type = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(nullable: true)]
    private ?int $sort_order = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): static
    {
        $this->race = $race;

        return $this;
    }

    public function getType(): ?AppearanceTypeEnum
    {
        return $this->type;
    }

    public function setType(AppearanceTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sort_order;
    }

    public function setSortOrder(?int $sort_order): static
    {
        $this->sort_order = $sort_order;

        return $this;
    }
}
