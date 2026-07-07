<?php

declare(strict_types=1);

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\ElixirDefinition;
use App\Security\LoggedInCharacter;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @implements ProcessorInterface<CharacterInventory, null>
 */
class CharacterInventorySellProcessor implements ProcessorInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $character = $this->loggedInCharacter->getCharacter();

        if ($data->getCharacter() !== $character) {
            throw new \Exception('Inventory slot does not belong to the logged-in character.');
        }

        $defaultGoldBuyPrice = $data->getItem()->getDefinition()->getBaseGoldPrice();
        $sellPrice = (int) ($defaultGoldBuyPrice * 0.75);

        $isElixir = $data->getItem()->getDefinition() instanceof ElixirDefinition;

        if ($isElixir) {
            $sellPrice = $sellPrice * $character->getLevel();
        }

        if ($isElixir && $data->getQuantity() > 1) {
            $data->setQuantity($data->getQuantity() - 1);
        } else {
            $this->entityManager->remove($data->getItem());
            $this->entityManager->remove($data);
        }

        $character->addGold($sellPrice);

        $this->entityManager->flush();

        return null;
    }
}
