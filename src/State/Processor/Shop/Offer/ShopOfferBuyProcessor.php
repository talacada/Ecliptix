<?php

namespace App\State\Processor\Shop\Offer;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Shop\ShopOffer;
use App\Repository\Character\CharacterInventoryRepository;
use App\Security\LoggedInCharacter;
use App\Service\Inventory\InventoryManager;
use App\Service\Item\ItemFactory;
use DateTime;
use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShopOfferBuyProcessor implements ProcessorInterface
{
    public function __construct(
        private LoggedInCharacter $loggedInCharacter,
        private CharacterInventoryRepository $characterInventoryRepository,
        private ItemFactory $itemFactory,
        private InventoryManager $inventoryManager,
    ){ }

    /**
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
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

        if ($character->getBackpackCapacity() <= count($this->characterInventoryRepository->findAllUnequipped($character))) {
            throw new Exception("Not enough backpack space");
        }

        $item = $this->itemFactory->createFromDefinitionAndOffer($data->getItemDefinition(), $data);

        //TODO add item to backpack,
        $inventory = $this->inventoryManager->addToBackpack($character, $item);

        //TODO subtract price,
        //TODO delete rotation

        dd($this->characterInventoryRepository->findAllUnequipped($character), $data->getRotation()->getValidFrom(), $data);
    }
}
