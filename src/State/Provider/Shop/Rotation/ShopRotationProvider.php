<?php

namespace App\State\Provider\Shop\Rotation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Security\LoggedInCharacter;

class ShopRotationProvider implements ProviderInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
    ) { }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $character = $this->loggedInCharacter->getCharacter();
    }
}
