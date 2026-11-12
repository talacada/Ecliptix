<?php

declare(strict_types=1);

namespace App\Character\Elixir;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Character\Character\LoggedInCharacter;
use App\Support\Exception\ResourceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements ProcessorInterface<ActiveElixir, null>
 */
class ActiveElixirRemoveProcessor implements ProcessorInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $character = $this->loggedInCharacter->getCharacter();

        if ($data->getCharacter() !== $character) {
            throw new ResourceNotFoundException(ActiveElixir::class);
        }

        $character->removeActiveElixir($data);

        $this->entityManager->flush();

        return null;
    }
}
