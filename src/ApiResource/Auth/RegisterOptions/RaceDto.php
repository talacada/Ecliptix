<?php

namespace App\ApiResource\Auth\RegisterOptions;

class RaceDto
{
    private int $id;
    private string $name;

    private AppearanceGroupDto $appearance;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
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

    public function getAppearance(): AppearanceGroupDto
    {
        return $this->appearance;
    }

    public function setAppearance(AppearanceGroupDto $appearance): void
    {
        $this->appearance = $appearance;
    }


}
