<?php

declare(strict_types=1);

namespace App\ApiResource\Item;

use App\Entity\Item\ElixirDefinition;
use App\Entity\Item\Item;
use App\Entity\Item\ItemDefinition;
use App\Entity\Item\ItemSlotEnum;
use Symfony\Component\Serializer\Attribute\Groups;

class ItemViewDTO
{
    public const string READ_GROUP = 'item:read';

    private ?int $id = null;
    #[Groups([self::READ_GROUP])]
    private string $name;
    #[Groups([self::READ_GROUP])]
    private ?string $description = null;
    #[Groups([self::READ_GROUP])]
    private ?int $damage = null;
    #[Groups([self::READ_GROUP])]
    private ?int $crit = null;
    #[Groups([self::READ_GROUP])]
    private ?int $health = null;
    #[Groups([self::READ_GROUP])]
    private int $requiredLevel;
    #[Groups([self::READ_GROUP])]
    private ItemSlotEnum $slot;
    #[Groups([self::READ_GROUP])]
    private ?string $elixirType = null;
    #[Groups([self::READ_GROUP])]
    private ?int $percentageBonus = null;
    #[Groups([self::READ_GROUP])]
    private ?int $durationSeconds = null;

    public function __construct(
    ) {
    }

    public function buildDtoOnlyWithBonusStats(ItemDefinition $definition, $bonusDamage, $bonusCrit, $bonusHealth): void
    {
        $this->setName($definition->getName());
        $this->setDescription($definition->getDescription());

        $this->setSlot($definition->getDesiredSlot());
        if ($definition instanceof ElixirDefinition) {
            $this->setElixirType($definition->getElixirType()->value);
            $this->setPercentageBonus($definition->getPercentageBonus());
            $this->setDurationSeconds($definition->getDurationSeconds());
        } else {
            // We want to have these stats NULL not 0 soo it's not displayed in DTO
            $this->setRequiredLevel($definition->getRequiredLevel());
            $this->setDamage($definition->getBaseDamage() + $bonusDamage);
            $this->setCrit($definition->getBaseCrit() + $bonusCrit);
            $this->setHealth($definition->getBaseHealth() + $bonusHealth);
        }
    }

    public function buildDtoFromItem(Item $item): void
    {
        $definition = $item->getDefinition();
        $this->setName($definition->getName());
        $this->setDescription($definition->getDescription());
        $this->setSlot($definition->getDesiredSlot());
        if ($definition instanceof ElixirDefinition) {
            $this->setElixirType($definition->getElixirType()->value);
            $this->setPercentageBonus($definition->getPercentageBonus());
            $this->setDurationSeconds($definition->getDurationSeconds());
        } else {
            $this->setRequiredLevel($definition->getRequiredLevel());
            $this->setDamage($definition->getBaseDamage() + $item->getBonusDamage());
            $this->setCrit($definition->getBaseCrit() + $item->getBonusCrit());
            $this->setHealth($definition->getBaseHealth() + $item->getBonusHealth());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDamage(): ?int
    {
        return $this->damage;
    }

    public function setDamage(?int $damage): void
    {
        $this->damage = $damage;
    }

    public function getCrit(): ?int
    {
        return $this->crit;
    }

    public function setCrit(?int $crit): void
    {
        $this->crit = $crit;
    }

    public function getHealth(): ?int
    {
        return $this->health;
    }

    public function setHealth(?int $health): void
    {
        $this->health = $health;
    }

    public function getRequiredLevel(): int
    {
        return $this->requiredLevel;
    }

    public function setRequiredLevel(int $requiredLevel): void
    {
        $this->requiredLevel = $requiredLevel;
    }

    public function getSlot(): ItemSlotEnum
    {
        return $this->slot;
    }

    public function setSlot(ItemSlotEnum $slot): void
    {
        $this->slot = $slot;
    }

    public function getElixirType(): ?string
    {
        return $this->elixirType;
    }

    public function setElixirType(?string $elixirType): void
    {
        $this->elixirType = $elixirType;
    }

    public function getPercentageBonus(): ?int
    {
        return $this->percentageBonus;
    }

    public function setPercentageBonus(?int $percentageBonus): void
    {
        $this->percentageBonus = $percentageBonus;
    }

    public function getDurationSeconds(): ?int
    {
        return $this->durationSeconds;
    }

    public function setDurationSeconds(?int $durationSeconds): void
    {
        $this->durationSeconds = $durationSeconds;
    }
}
