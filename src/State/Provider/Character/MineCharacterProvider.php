<?php

namespace App\State\Provider\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Character\Character;
use App\Security\LoggedInCharacter;

class MineCharacterProvider implements ProviderInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
    ) {
    }

    /**
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return Character
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Character
    {
        return $this->loggedInCharacter->getCharacter();
    }
}
