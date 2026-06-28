<?php

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\ElixirDefinition;
use App\Security\LoggedInCharacter;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CharacterInventorySellProcessor implements ProcessorInterface
{

    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws Exception
     */
    //TODO in future will take care of quantity items
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof CharacterInventory);

        $character = $this->loggedInCharacter->getCharacter();

        if ($data->getCharacter() !== $character) {
            throw new Exception("Inventory slot does not belong to the logged-in character.");
        }

        $defaultGoldBuyPrice = $data->getItem()->getDefinition()->getBaseGoldPrice();
        $sellPrice = (int)($defaultGoldBuyPrice * 0.75);

        $isElixir = $data->getItem()->getDefinition() instanceof ElixirDefinition;


        if ($isElixir && $data->getQuantity() > 1) {
            $data->setQuantity($data->getQuantity() - 1);
            $sellPrice = $sellPrice * $character->getLevel();
        }else{
            $this->entityManager->remove($data->getItem());
            $this->entityManager->remove($data);
        }

        $character->addGold($sellPrice);

        $this->entityManager->flush();
    }
}
