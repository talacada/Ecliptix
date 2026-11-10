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
    ) {
    }

    /**
     * @return ShopRotation[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $character = $this->loggedInCharacter->getCharacter();

        $allShopRotations = $this->shopRotationRepository->findAllByCharacter($character);

        return $allShopRotations;
    }
}
