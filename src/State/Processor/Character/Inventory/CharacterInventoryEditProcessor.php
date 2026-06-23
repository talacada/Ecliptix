<?php

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\CharacterInventory;
use App\Repository\Character\CharacterInventoryRepository;
use App\Security\LoggedInCharacter;
use App\Service\Inventory\InventoryManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CharacterInventoryEditProcessor implements ProcessorInterface
{
    //TODO udělat CharacterInventoryContainer více v claude -c

    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterInventoryRepository $characterInventoryRepository,
        private InventoryManager $inventoryManager,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CharacterInventory
    {
        assert($data instanceof CharacterInventory);

        $character = $this->loggedInCharacter->getCharacter();

        if ($data->getPosition() > $character->getBackpackCapacity()) {
            throw new Exception("Position exceeds backpack capacity.");
        }

        //User must send [equipped = true, position = 0]
        if ($data->isEquipped() && $data->getPosition() !== 0) {
            $data->setPosition(0);
        }

        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);
        $wasEquipped = $originalData['equipped'];
        $wasPosition = $originalData['position'];

        if ($wasPosition != $data->getPosition()) {
            if ($data->getPosition() === 0) {
                $itemToMove = $this->inventoryManager->getEquippedItemBySlot($character, $data->getItem()->getDefinition()->getDesiredSlot());
            }else {
                $itemToMove = $this->characterInventoryRepository->getOneByPosition(
                    $character,
                    $data->getPosition()
                );
            }
            //Position changed AND equipped did not change
            if ($wasEquipped === $data->isEquipped()) {
                //Change position with item that was there
                $itemToMove?->setPosition($wasPosition);
            }else {
                if ($itemToMove !== null) {
                    //Changing from equipped to unequipped with specific slot, meaning swaping equipped items
                    if ($itemToMove->getItem()->getDefinition()->getDesiredSlot() === $data->getItem()->getDefinition()->getDesiredSlot()) {
                        $itemToMove->setPosition($wasPosition);
                        $itemToMove->setEquipped($wasEquipped);
                    }else{
                        throw new Exception(sprintf(
                            "Items does not match slots. Equipped item type %s cant swap with %s",
                            $data->getItem()->getDefinition()->getDesiredSlot()->value,
                            $itemToMove->getItem()->getDefinition()->getDesiredSlot()->value
                        ));
                    }
                }
            }
        //User only send change equipped but not position
        }else {
            //From equipped to unequipped, first possible position
            if ($wasEquipped && $data->isEquipped() === false) {
                $firstAvailablePosition = $this->inventoryManager->getFirstAvailablePosition($character);

                $data->setPosition($firstAvailablePosition);
            //From unequipped to equipped
            }elseif (!$wasEquipped && $data->isEquipped() === true) {
                $equippedItem = $this->inventoryManager->getEquippedItemBySlot($character, $data->getItem()->getDefinition()->getDesiredSlot());
                if ($equippedItem !== null) {
                    $equippedItem->setPosition($wasPosition);
                    $equippedItem->setEquipped(false);
                }
            }
        }

        $this->entityManager->flush();
        return $data;
    }
}
