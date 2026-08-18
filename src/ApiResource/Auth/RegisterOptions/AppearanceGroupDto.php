<?php

declare(strict_types=1);

namespace App\ApiResource\Auth\RegisterOptions;

class AppearanceGroupDto
{
    /** @var AppearanceOptionDto[] */
    private array $hair;

    /** @var AppearanceOptionDto[] */
    private array $eyes;

    /** @var AppearanceOptionDto[] */
    private array $mouth;

    /** @var AppearanceOptionDto[] */
    private array $nose;

    /** @var AppearanceOptionDto[] */
    private array $ears;

    public function __construct()
    {
        $this->hair = [];
        $this->eyes = [];
        $this->mouth = [];
        $this->nose = [];
        $this->ears = [];
    }

    /**
     * @return AppearanceOptionDto[]
     */
    public function getHair(): array
    {
        return $this->hair;
    }

    /**
     * @param AppearanceOptionDto[] $hair
     */
    public function setHair(array $hair): void
    {
        $this->hair = $hair;
    }

    /**
     * @return AppearanceOptionDto[]
     */
    public function getEyes(): array
    {
        return $this->eyes;
    }

    /**
     * @param AppearanceOptionDto[] $eyes
     */
    public function setEyes(array $eyes): void
    {
        $this->eyes = $eyes;
    }

    /**
     * @return AppearanceOptionDto[]
     */
    public function getMouth(): array
    {
        return $this->mouth;
    }

    /**
     * @param AppearanceOptionDto[] $mouth
     */
    public function setMouth(array $mouth): void
    {
        $this->mouth = $mouth;
    }

    /**
     * @return AppearanceOptionDto[]
     */
    public function getNose(): array
    {
        return $this->nose;
    }

    /**
     * @param AppearanceOptionDto[] $nose
     */
    public function setNose(array $nose): void
    {
        $this->nose = $nose;
    }

    /**
     * @return AppearanceOptionDto[]
     */
    public function getEars(): array
    {
        return $this->ears;
    }

    /**
     * @param AppearanceOptionDto[] $ears
     */
    public function setEars(array $ears): void
    {
        $this->ears = $ears;
    }

    public function addHair(AppearanceOptionDto $optionDto): void
    {
        $this->hair[] = $optionDto;
    }

    public function addEars(AppearanceOptionDto $optionDto): void
    {
        $this->ears[] = $optionDto;
    }

    public function addMouth(AppearanceOptionDto $optionDto): void
    {
        $this->mouth[] = $optionDto;
    }

    public function addNose(AppearanceOptionDto $optionDto): void
    {
        $this->nose[] = $optionDto;
    }

    public function addEyes(AppearanceOptionDto $optionDto): void
    {
        $this->eyes[] = $optionDto;
    }
}
