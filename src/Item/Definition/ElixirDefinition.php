<?php

declare(strict_types=1);

namespace App\Item\Definition;

use App\Shop\Rotation\ShopRotation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
class ElixirDefinition extends ItemDefinition
{
    #[ORM\Column(nullable: true, enumType: ElixirTypeEnum::class)]
    #[Groups(ShopRotation::READ_GROUP)]
    private ElixirTypeEnum $elixirType;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $percentageBonus;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(ShopRotation::READ_GROUP)]
    private int $durationSeconds;

    public function __construct()
    {
        parent::__construct();
        $this->setBaseDamage(0);
        $this->setBaseCrit(0);
        $this->setBaseHealth(0);
        $this->setRequiredLevel(1);
        $this->setDesiredSlot(ItemSlotEnum::Elixir);
        $this->setRarity(ItemRarityEnum::Common);
    }

    #[Override]
    public function isStackable(): bool
    {
        return true;
    }

    public function getElixirType(): ElixirTypeEnum
    {
        return $this->elixirType;
    }

    public function setElixirType(ElixirTypeEnum $elixirType): static
    {
        $this->elixirType = $elixirType;

        return $this;
    }

    public function getPercentageBonus(): int
    {
        return $this->percentageBonus;
    }

    public function setPercentageBonus(int $percentageBonus): static
    {
        $this->percentageBonus = $percentageBonus;

        return $this;
    }

    public function getDurationSeconds(): int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(int $durationSeconds): static
    {
        $this->durationSeconds = $durationSeconds;

        return $this;
    }
}
