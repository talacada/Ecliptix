<?php

declare(strict_types=1);

namespace App\State\Provider\Character\Elixir;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ActiveElixirRepository;
use App\Security\LoggedInCharacter;
use App\Service\Elixir\ElixirCleanUp;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActiveElixirProvider implements ProviderInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private ActiveElixirRepository $activeElixirRepository,
        private ElixirCleanUp $elixirCleanUp,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $character = $this->loggedInCharacter->getCharacter();

        $this->elixirCleanUp->removeExpired($character);

        $elixir = $this->activeElixirRepository->findOneById($uriVariables['id']);

        if (!$elixir) {
            throw new NotFoundHttpException('Not Found');
        }
        if ($elixir->getCharacter() !== $character) {
            throw new NotFoundHttpException('Not Found');
        }

        return $elixir;
    }
}
