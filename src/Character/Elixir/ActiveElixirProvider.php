<?php

declare(strict_types=1);

namespace App\Character\Elixir;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Character\Character\LoggedInCharacter;
use App\Exception\ResourceNotFoundException;

/**
 * @implements ProviderInterface<ActiveElixir>
 */
class ActiveElixirProvider implements ProviderInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private ActiveElixirRepository $activeElixirRepository,
        private ElixirCleanUp $elixirCleanUp,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ActiveElixir
    {
        $character = $this->loggedInCharacter->getCharacter();

        $this->elixirCleanUp->removeExpired($character);

        $rawId = $uriVariables['id'];

        if (!is_numeric($rawId)) {
            throw new ResourceNotFoundException(ActiveElixir::class, $rawId);
        }

        $elixir = $this->activeElixirRepository->findOneById((int) $rawId);

        if (!$elixir) {
            throw new ResourceNotFoundException(ActiveElixir::class, intval($rawId));
        }
        if ($elixir->getCharacter() !== $character) {
            throw new ResourceNotFoundException(ActiveElixir::class, intval($rawId));
        }

        return $elixir;
    }
}
