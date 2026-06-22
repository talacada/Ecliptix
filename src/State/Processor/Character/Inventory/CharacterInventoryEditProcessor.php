<?php

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\CharacterInventory;
use App\Repository\Character\CharacterInventoryRepository;
use App\Security\LoggedInCharacter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CharacterInventoryEditProcessor implements ProcessorInterface
{

    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterInventoryRepository $characterInventoryRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof CharacterInventory);

        $character = $this->loggedInCharacter->getCharacter();

        if ($data->getPosition() > $character->getBackpackCapacity()) {
            throw new Exception("Position exceeds backpack capacity.");
        }

        //TODO kdyz se zmeni pozice na neco kde uz nejaky item byl itemy se prohodi
        //TODO itemy co jsou equipnuté ztrácí pozici a dostávají pozici 0
        //TODO equipnutý může být jenom jeden meč... a když se dam equip na druhý meč tak se prohodí s tím co je equipnutý a ten co byl equipnutý
        $itemToMove = $this->characterInventoryRepository->getOneByPosition(
            $character,
            $data->getPosition()
        );


    }
}
