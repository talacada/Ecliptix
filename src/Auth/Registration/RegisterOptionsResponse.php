<?php

declare(strict_types=1);

namespace App\Auth\Registration;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;

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
