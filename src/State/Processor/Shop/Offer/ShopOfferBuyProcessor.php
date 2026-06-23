<?php

namespace App\State\Processor\Shop\Offer;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Item\ItemViewDTO;
use App\Entity\Shop\ShopOffer;
use App\Repository\Character\CharacterInventoryRepository;
use App\Security\LoggedInCharacter;
use App\Service\Inventory\InventoryManager;
use App\Service\Item\ItemFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShopOfferBuyProcessor implements ProcessorInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterInventoryRepository $characterInventoryRepository,
        private ItemFactory $itemFactory,
        private InventoryManager $inventoryManager,
        private EntityManagerInterface $entityManager
    ){ }

    /**
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ItemViewDTO
    {
        assert($data instanceof ShopOffer);
        $character = $this->loggedInCharacter->getCharacter();

        $rotation = $data->getRotation();

        if ($rotation->getCharacter() !== $character) {
            throw new NotFoundHttpException('Rotation not found');
        }

        if ($rotation->getValidUntil() < new DateTime() || $rotation->getValidFrom() > new DateTime()) {
            throw new Exception("Rotation not available");
        }

        if ($data->getGoldPrice() > $character->getGold()) {
            throw new Exception("Not enough gold");
        }

        if ($data->getDiamondPrice() > $character->getDiamonds()) {
            throw new Exception("Not enough diamonds");
        }

        if ($character->getBackpackCapacity() <= count($this->characterInventoryRepository->getUnequippedItems($character))) {
            throw new Exception("Not enough backpack space");
        }

        $item = $this->itemFactory->createFromDefinitionAndOffer($data->getItemDefinition(), $data);

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
