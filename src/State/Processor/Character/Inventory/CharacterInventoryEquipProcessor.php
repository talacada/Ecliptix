<?php

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\CharacterInventory;
use App\Security\LoggedInCharacter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Exception;

class CharacterInventoryEquipProcessor implements ProcessorInterface
{

    public function __construct(
       private LoggedInCharacter $loggedInCharacter,
        private EntityManagerInterface $entityManager,
    ){ }

    /**
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof CharacterInventory);
        $character = $this->loggedInCharacter->getCharacter();

        if ($data->getCharacter() !== $character) {
            throw new Exception("Inventory slot does not belong to the logged-in character.");
        }

        $updatedStatus = !$data->isEquipped();
        $data->setEquipped($updatedStatus);
        $this->entityManager->flush();

        return $data;
    }
}
