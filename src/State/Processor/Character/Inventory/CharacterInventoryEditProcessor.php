<?php

namespace App\State\Processor\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Character\CharacterInventory;
use App\Entity\Item\ElixirDefinition;
use App\Entity\Item\InventoryContainerEnum;
use App\Repository\Character\CharacterInventoryRepository;
use App\Security\LoggedInCharacter;
use App\Service\Inventory\InventoryManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CharacterInventoryEditProcessor implements ProcessorInterface
{


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

        if ($data->getItem()->getDefinition() instanceof ElixirDefinition && $data->getContainer() === InventoryContainerEnum::Equipped) {
            throw new Exception("Elixir cant be equipped.");
        }

        $originalData = $this->entityManager->getUnitOfWork()->getOriginalEntityData($data);
        $wasContainer = $originalData['container'];
        $wasPosition = $originalData['position'];

        //User must send [equipped = true, position = 0]
        if (InventoryContainerEnum::Equipped === $data->getContainer() && $data->getPosition() !== 0) {
            // Client explicitly sent position != 0 with equip → error
            if ($wasPosition !== $data->getPosition()) {
                throw new Exception("Equipped items do not have position.");
            }
            // Client didn't send position, item was in backpack → auto-set to 0
            $data->setPosition(0);
        }

        if ($wasPosition != $data->getPosition()) {
            if (InventoryContainerEnum::Equipped === $data->getContainer()) {
                $itemToMove = $this->inventoryManager->getEquippedItemBySlot($character, $data->getItem()->getDefinition()->getDesiredSlot());
            }else {
                $itemToMove = $this->characterInventoryRepository->getOneByPosition(
                    $character,
                    $data->getPosition()
                );
            }
            //Position changed AND equipped did not change
            if ($wasContainer === $data->getContainer()) {
                //Change position with item that was there
                $itemToMove?->setPosition($wasPosition);
            }else {
                if ($itemToMove !== null) {
                    //Changing from equipped to unequipped with specific slot, meaning swaping equipped items
                    if ($itemToMove->getItem()->getDefinition()->getDesiredSlot() === $data->getItem()->getDefinition()->getDesiredSlot()) {
                        $itemToMove->setPosition($wasPosition);
                        $itemToMove->setContainer($wasContainer);
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
            if (InventoryContainerEnum::Equipped === $wasContainer && InventoryContainerEnum::Backpack === $data->getContainer()) {
                $firstAvailablePosition = $this->inventoryManager->getFirstAvailablePosition($character);

                $data->setPosition($firstAvailablePosition);
            //From unequipped to equipped
            }elseif (InventoryContainerEnum::Backpack === $wasContainer && InventoryContainerEnum::Equipped === $data->getContainer()) {
                $equippedItem = $this->inventoryManager->getEquippedItemBySlot($character, $data->getItem()->getDefinition()->getDesiredSlot());
                if ($equippedItem !== null) {
                    $equippedItem->setPosition($wasPosition);
                    $equippedItem->setContainer(InventoryContainerEnum::Backpack);
                }
            }
        }

        $this->entityManager->flush();
        return $data;
    }
}
