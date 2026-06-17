<?php

namespace App\State\Provider\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Security\LoggedInCharacter;

class CharacterInventoryProvider implements ProviderInterface
{

    public function __construct(
        private loggedInCharacter $loggedInCharacter
    ){ }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $character = $this->loggedInCharacter->getCharacter();

        dd($character);
    }
}
