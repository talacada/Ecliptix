<?php

declare(strict_types=1);

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ActiveElixir;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\ElixirDefinition;
use App\Repository\Character\ActiveElixirRepository;
use App\Security\LoggedInCharacter;
use App\Service\Elixir\ElixirCleanUp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProcessorInterface<CharacterInventory, ActiveElixir>
 */
class CharacterInventoryUseProcessor implements ProcessorInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private ActiveElixirRepository $activeElixirRepository,
        private EntityManagerInterface $entityManager,
        private ElixirCleanUp $elixirCleanUp,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ActiveElixir
    {
        $character = $this->loggedInCharacter->getCharacter();
        $definition = $data->getItem()->getDefinition();

        if (!$definition instanceof ElixirDefinition) {
            throw new \Exception('Can use activate only elixirs');
        }

        if ($data->getCharacter() !== $character) {
            throw new NotFoundHttpException('Not found');
        }

        $this->elixirCleanUp->removeExpired($character);

        $existingSameElixir = $this->activeElixirRepository->findByName($definition->getName(), $character);

        if (null === $existingSameElixir && count($character->getActiveElixirs()) >= 3) {
            throw new \Exception('Cant have more than 3 elixirs activated');
        }

        if (null !== $existingSameElixir) {
            $existingSameElixir->setExpiresAt(
                $existingSameElixir->getExpiresAt()->modify('+'.$definition->getDurationSeconds().' seconds'),
            );
            $activeElixir = $existingSameElixir;
        } else {
            $activeElixir = new ActiveElixir();
            $activeElixir->setCharacter($character);
            $activeElixir->setItemDefinition($definition);
            $activeElixir->setExpiresAt(new \DateTimeImmutable()->modify(
                '+'.$definition->getDurationSeconds().' seconds', ),
            );
            $this->entityManager->persist($activeElixir);
        }

        if ($data->getQuantity() > 1) {
            $data->setQuantity($data->getQuantity() - 1);
        } else {
            $this->entityManager->remove($data->getItem());
            $this->entityManager->remove($data);
        }

        $this->entityManager->flush();

        return $activeElixir;
    }
}
