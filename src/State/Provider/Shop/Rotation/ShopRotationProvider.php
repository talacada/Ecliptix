<?php

namespace App\State\Provider\Shop\Rotation;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ShopRotationProvider implements ProviderInterface
{
    public function __construct(
        private Security $security,
    ) { }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        //TODO udelat nejaky handy interface
        $character = $this->security->getUser();
    }
}
