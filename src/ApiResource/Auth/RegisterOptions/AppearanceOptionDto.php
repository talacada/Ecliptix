<?php

namespace App\ApiResource\Auth\RegisterOptions;

use App\Entity\AppearanceOption;

class AppearanceOptionDto
{
    private int $id;

    private string $label;

    private int $sortOrder;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public static function fromEntity(AppearanceOption $entity): self
    {
        $dto = new self();
        $dto->id = $entity->getId();
        $dto->label = $entity->getLabel();
        $dto->sortOrder = $entity->getSortOrder() ?? 0;
        return $dto;
    }

}
