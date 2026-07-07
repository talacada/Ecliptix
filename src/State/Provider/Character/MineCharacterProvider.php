<?php

declare(strict_types=1);

namespace App\State\Provider\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Character\Character;
use App\Security\LoggedInCharacter;
use App\Service\Elixir\ElixirCleanUp;

class MineCharacterProvider implements ProviderInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private ElixirCleanUp $elixirCleanUp,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Character
    {
        $character = $this->loggedInCharacter->getCharacter();

        $this->elixirCleanUp->removeExpired($character);

        return $character;
    }
}
