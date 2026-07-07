<?php

declare(strict_types=1);

namespace App\State\Provider\Shop\Rotation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\Shop\ShopRotationRepository;
use App\Security\LoggedInCharacter;

class ShopRotationProvider implements ProviderInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private ShopRotationRepository $shopRotationRepository,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $character = $this->loggedInCharacter->getCharacter();

        $allShopRotations = $this->shopRotationRepository->findAllByCharacter($character);

        return $allShopRotations;
    }
}
