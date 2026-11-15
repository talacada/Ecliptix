<?php

declare(strict_types=1);

namespace App\Character\Character;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Character\Elixir\ElixirCleanUp;

/**
 * @implements ProviderInterface<Character>
 */
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
