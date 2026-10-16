<?php

declare(strict_types=1);

namespace App\ApiResource\Auth\RegisterOptions;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\State\Provider\Auth\RegisterOptionsProvider;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/auth/register/options',
            provider: RegisterOptionsProvider::class,
        ),
    ],
)]
class RegisterOptionsResponse
{
    /** @var RaceDto[] */
    private array $races = [];

    /**
     * @return RaceDto[]
     */
    public function getRaces(): array
    {
        return $this->races;
    }

    /**
     * @param RaceDto[] $races
     */
    public function setRaces(array $races): void
    {
        $this->races = $races;
    }
}
