<?php

declare(strict_types=1);

namespace App\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Character\Character\LoggedInCharacter;
use App\Support\Exception\ResourceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

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
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $character = $this->loggedInCharacter->getCharacter();

        if ($data->getCharacter() !== $character) {
            throw new ResourceNotFoundException(CharacterInventory::class);
        }

        $isStackable = $data->getItem()->getDefinition()->isStackable();

        if ($isStackable && $data->getQuantity() > 1) {
            $willGetGold = $data->removeGoldSellPriceByUnits(1);
            $willGetDia = $data->removeDiamondSellPriceByUnits(1);
            $character->addGold($willGetGold);
            $character->addDiamonds($willGetDia);

            $data->removeQuantity(1);
        } else {
            $character->addGold($data->getGoldSellPrice());
            $character->addDiamonds($data->getDiamondSellPrice());

            $this->entityManager->remove($data->getItem());
            $this->entityManager->remove($data);
        }

        $this->entityManager->flush();

        return null;
    }
}
