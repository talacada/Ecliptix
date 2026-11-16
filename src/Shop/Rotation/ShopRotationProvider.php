<?php

declare(strict_types=1);

namespace App\Shop\Rotation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Character\Character\LoggedInCharacter;

/**
 * @implements ProviderInterface<ShopRotation>
 */
class ShopRotationProvider implements ProviderInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private ShopRotationRepository $shopRotationRepository,
        private readonly RotationGenerator $rotationGenerator,
    ) {
    }

    /**
     * @return ShopRotation[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $character = $this->loggedInCharacter->getCharacter();

        if (false === $this->shopRotationRepository->hasActiveDailyRotation($character)) {
            $this->rotationGenerator->generateDaily($character);
        }

        return $this->shopRotationRepository->findAllByCharacter($character);
    }
}
