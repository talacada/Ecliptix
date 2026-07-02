<?php

namespace App\State\Provider\Character\Elixir;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\ActiveElixir;
use App\Repository\ActiveElixirRepository;
use App\Security\LoggedInCharacter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ActiveElixirProvider implements ProviderInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private ActiveElixirRepository $activeElixirRepository,
    ){}
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $character = $this->loggedInCharacter->getCharacter();

        //TODO make
        //$this->elixirCleaUp->removeExpired($character);

        $elixir = $this->activeElixirRepository->findOneById($uriVariables['id']);

        if (!$elixir) throw new NotFoundHttpException('Not Found');
        if ($elixir->getCharacter() !== $character) throw new NotFoundHttpException('Not Found');

        return $elixir;
    }
}
