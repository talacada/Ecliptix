<?php

declare(strict_types=1);

namespace App\State\Provider\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Item\ItemViewDTO;
use App\Entity\Character\CharacterInventory;
use App\Exception\ResourceNotFoundException;
use App\Repository\Character\CharacterInventoryRepository;
use App\Security\LoggedInCharacter;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

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

            if ($inventorySlot === null) {
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
