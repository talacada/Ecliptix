<?php

declare(strict_types=1);

namespace App\State\Processor\Shop\Offer;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Item\ItemViewDTO;
use App\Entity\Item\ElixirDefinition;
use App\Entity\Shop\ShopOffer;
use App\Entity\Shop\ShopRotation;
use App\Exception\Character\InsufficientDiamondsException;
use App\Exception\Character\InsufficientGoldException;
use App\Exception\Inventory\BackpackFullException;
use App\Exception\ResourceNotFoundException;
use App\Exception\Shop\ShopRotationNotAvailableException;
use App\Factory\ItemFactory;
use App\Repository\Character\CharacterInventoryRepository;
use App\Security\LoggedInCharacter;
use App\Service\Inventory\InventoryManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

/**
 * @implements ProcessorInterface<ShopOffer, ItemViewDTO>
 */
class ShopOfferBuyProcessor implements ProcessorInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterInventoryRepository $characterInventoryRepository,
        private ItemFactory $itemFactory,
        private InventoryManager $inventoryManager,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ItemViewDTO
    {
        $character = $this->loggedInCharacter->getCharacter();

        $rotation = $data->getRotation();

        if ($rotation->getCharacter() !== $character) {
            throw new ResourceNotFoundException(ShopRotation::class);
        }

        if ($rotation->getValidUntil() < new DateTime() || $rotation->getValidFrom() > new DateTime()) {
            throw new ShopRotationNotAvailableException();
        }

        if ($data->getGoldPrice() > $character->getGold()) {
            throw new InsufficientGoldException($data->getGoldPrice(), $character->getGold());
        }

        if ($data->getDiamondPrice() > $character->getDiamonds()) {
            throw new InsufficientDiamondsException($data->getDiamondPrice(), $character->getDiamonds());
        }

        $isElixir = $data->getItemDefinition() instanceof ElixirDefinition;

        if (!$isElixir && $character->getBackpackCapacity() <= count($this->characterInventoryRepository->getUnequippedItems($character))) {
            throw new BackpackFullException();
        }

        $item = $this->itemFactory->createFromDefinitionAndOffer($data);

        $inventory = $this->inventoryManager->addToBackpack($character, $item);

        $character->subtractGold($data->getGoldPrice());
        $character->subtractDiamonds($data->getDiamondPrice());

        $itemDto = new ItemViewDTO();
        $itemDto->buildDtoFromItem($item);

        $this->entityManager->remove($data);

        $this->entityManager->persist($item);
        $this->entityManager->persist($inventory);
        $this->entityManager->flush();

        return $itemDto;
    }
}
