<?php

namespace App\State\Provider\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Item\ItemViewDTO;
use App\Repository\Character\CharacterInventoryRepository;
use App\Security\LoggedInCharacter;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CharacterInventoryProvider implements ProviderInterface
{

    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterInventoryRepository $characterInventoryRepository
    ){ }

    /**
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $character = $this->loggedInCharacter->getCharacter();

        if (isset($uriVariables['inventoryId'])) {
            $inventoryId = $uriVariables['inventoryId'];
            $inventorySlot = $this->characterInventoryRepository->getInventoryById($inventoryId);

            if ($inventorySlot === null) {
                throw new NotFoundHttpException("Inventory slot not found.");
            }

            if ($inventorySlot->getCharacter() !== $character) {
                throw new Exception("Inventory slot does not belong to the logged-in character.");
            }


            $dto = new ItemViewDTO();
            $dto->buildDtoFromItem($inventorySlot->getItem());
            $inventorySlot->setItemViewDTO($dto);

            return $inventorySlot;
        }else {
            $inventories = $character->getCharacterInventories();

            foreach ($inventories as $inv) {
                $dto = new ItemViewDTO();
                $dto->buildDtoFromItem(
                    $inv->getItem()
                );
                $inv->setItemViewDTO($dto);
            }

            return $inventories->toArray();
        }
    }
}
