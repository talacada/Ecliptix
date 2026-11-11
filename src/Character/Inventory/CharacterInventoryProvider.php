<?php

declare(strict_types=1);

namespace App\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Character\Character\LoggedInCharacter;
use App\Item\Item\ItemViewDTO;
use App\Support\Exception\ResourceNotFoundException;
use Exception;

/**
 * @implements ProviderInterface<CharacterInventory>
 */
class CharacterInventoryProvider implements ProviderInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterInventoryRepository $characterInventoryRepository,
    ) {
    }

    /**
     * @return CharacterInventory[]|CharacterInventory
     *
     * @throws Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CharacterInventory|array
    {
        $character = $this->loggedInCharacter->getCharacter();

        if (isset($uriVariables['inventoryId'])) {
            $rawId = $uriVariables['inventoryId'];
            if (!is_numeric($rawId)) {
                throw new ResourceNotFoundException(CharacterInventory::class, $rawId);
            }
            $inventorySlot = $this->characterInventoryRepository->getInventoryById((int) $rawId);

            if (null === $inventorySlot) {
                throw new ResourceNotFoundException(CharacterInventory::class, intval($rawId));
            }

            if ($inventorySlot->getCharacter() !== $character) {
                throw new ResourceNotFoundException(CharacterInventory::class, intval($rawId));
            }

            $dto = new ItemViewDTO();
            $dto->buildDtoFromItem($inventorySlot->getItem());
            $inventorySlot->setItemViewDTO($dto);

            return $inventorySlot;
        }
        $inventories = $character->getCharacterInventories();

        foreach ($inventories as $inv) {
            $dto = new ItemViewDTO();
            $dto->buildDtoFromItem(
                $inv->getItem(),
            );
            $inv->setItemViewDTO($dto);
        }

        return $inventories->toArray();
    }
}
