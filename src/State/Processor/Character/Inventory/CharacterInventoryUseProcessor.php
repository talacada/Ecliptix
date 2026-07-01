<?php

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\ActiveElixir;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\ElixirDefinition;
use App\Repository\ActiveElixirRepository;
use App\Security\LoggedInCharacter;
use DateMalformedStringException;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CharacterInventoryUseProcessor implements ProcessorInterface
{

    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private ActiveElixirRepository $activeElixirRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof CharacterInventory);
        $character = $this->loggedInCharacter->getCharacter();
        $definition = $data->getItem()->getDefinition();

        if (!$definition instanceof ElixirDefinition) throw new Exception("Can use activate only elixirs");

        if ($data->getCharacter() !== $character) throw new NotFoundHttpException("Not found");

        //$this->elixirCleaUp->removeExpired($character);

        $existingSameElixir = $this->activeElixirRepository->findByName($definition->getName(), $character);

        if ($existingSameElixir === null && count($character->getActiveElixirs()) >= 3) {
            throw new Exception("Cant have more than 3 elixirs activated");
        }

        if ($existingSameElixir !== null) {
            $existingSameElixir->setExpiresAt(
                $existingSameElixir->getExpiresAt()->modify('+' . $definition->getDurationSeconds() . ' seconds')
            );
        } else {
            $activeElixir = new ActiveElixir();
            $activeElixir->setCharacter($character);
            $activeElixir->setItemDefinition($definition);
            $activeElixir->setExpiresAt(new DateTimeImmutable()->modify(
                    '+' . $definition->getDurationSeconds() . ' seconds'
                )
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
    }
}
