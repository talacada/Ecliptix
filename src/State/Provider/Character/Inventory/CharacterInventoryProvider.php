<?php

declare(strict_types=1);

namespace App\State\Provider\Character\Inventory;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Item\ItemViewDTO;
use App\Entity\Character\CharacterInventory;
use App\Repository\Character\CharacterInventoryRepository;
use App\Security\LoggedInCharacter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @throws \Exception
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CharacterInventory|array
    {
        $character = $this->loggedInCharacter->getCharacter();

        if (isset($uriVariables['inventoryId'])) {
            $rawId = $uriVariables['inventoryId'];
            if (!is_numeric($rawId)) {
                throw new NotFoundHttpException('Invalid inventory ID.');
            }
            $inventorySlot = $this->characterInventoryRepository->getInventoryById((int)$rawId);

            if (null === $inventorySlot) {
                throw new NotFoundHttpException('Inventory slot not found.');
            }

            if ($inventorySlot->getCharacter() !== $character) {
                throw new \Exception('Inventory slot does not belong to the logged-in character.');
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
