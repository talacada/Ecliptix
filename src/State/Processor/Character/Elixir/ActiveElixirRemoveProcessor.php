<?php

declare(strict_types=1);

namespace App\State\Processor\Character\Elixir;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ActiveElixir;
use App\Security\LoggedInCharacter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            throw new NotFoundHttpException('Not found');
        }

        $this->entityManager->remove($data);
        $this->entityManager->flush();

        return null;
    }
}
