<?php

namespace App\ApiResource\Auth\RegisterOptions;

class RaceDto
{
    private int $id;
    private string $name;

    /* @var AppearanceGroupDto[] */
    private array $appearances;

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

    public function getAppearances(): array
    {
        return $this->appearances;
    }

    public function setAppearances(array $appearances): void
    {
        $this->appearances = $appearances;
    }


}
