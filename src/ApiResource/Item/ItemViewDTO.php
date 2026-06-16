<?php

namespace App\ApiResource\Item;

use App\Entity\Item\Item;
use App\Entity\Item\ItemDefinition;
use App\Entity\Shop\ShopRotation;
use Symfony\Component\Serializer\Attribute\Groups;

class ItemViewDTO
{
    #[Groups(ShopRotation::READ_GROUP)]
    private ?int $id = null;
    #[Groups(ShopRotation::READ_GROUP)]
    private string $name;
    #[Groups(ShopRotation::READ_GROUP)]
    private ?string $description = null;
    #[Groups(ShopRotation::READ_GROUP)]
    private int $damage;
    #[Groups(ShopRotation::READ_GROUP)]
    private int $crit;
    #[Groups(ShopRotation::READ_GROUP)]
    private int $health;
    #[Groups(ShopRotation::READ_GROUP)]
    private int $requiredLevel;
    public function __construct(
    ) {
    }

    public function buildDtoOnlyWithBonusStats(ItemDefinition $definition, $bonusDamage, $bonusCrit, $bonusHealth): void
    {
        $this->setName($definition->getName());
        $this->setDescription($definition->getDescription());
        $this->setRequiredLevel($definition->getRequiredLevel());
        $this->setDamage($definition->getBaseDamage() + $bonusDamage);
        $this->setCrit($definition->getBaseCrit() + $bonusCrit);
        $this->setHealth($definition->getBaseHealth() + $bonusHealth);
    }
    public function buildDtoFromDefinitionAndItem(ItemDefinition $definition, Item $item): void
    {
        $this->setName($definition->getName());
        $this->setDescription($definition->getDescription());
        $this->setRequiredLevel($definition->getRequiredLevel());
        $this->setDamage($definition->getBaseDamage() + $item->getBonusDamage());
        $this->setCrit($definition->getBaseCrit() + $item->getBonusCrit());
        $this->setHealth($definition->getBaseHealth() + $item->getBonusHealth());
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

    public function getDamage(): int
    {
        return $this->damage;
    }

    public function setDamage(int $damage): void
    {
        $this->damage = $damage;
    }

    public function getCrit(): int
    {
        return $this->crit;
    }

    public function setCrit(int $crit): void
    {
        $this->crit = $crit;
    }

    public function getHealth(): int
    {
        return $this->health;
    }

    public function setHealth(int $health): void
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


}
