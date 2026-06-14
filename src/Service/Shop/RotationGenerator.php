<?php

namespace App\Service\Shop;

use App\Entity\Character\Character;
use App\Entity\Shop\ShopOffer;
use App\Entity\Shop\ShopRotation;
use App\Entity\Shop\ShopRotationEnum;
use \App\Repository\Item\ItemDefinitionRepository;
use App\Repository\Shop\ShopRotationRepository;
use App\Service\Item\ItemFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class RotationGenerator {

    function __construct(
        private ItemDefinitionRepository $itemDefinitionRepository,
        private EntityManagerInterface $entityManager,
        private ShopRotationRepository $shopRotationRepository,
        private ItemFactory $itemFactory,
    ){ }
    public function generate(Character $character):ShopRotation
    {
        $oldRotations = $this->shopRotationRepository->findAllExpired($character);
        foreach ($oldRotations as $rotation) {
            $this->entityManager->remove($rotation);
        }
        $this->entityManager->flush();

        $shopRotation = new ShopRotation();
        $shopRotation->setCharacter($character);
        $shopRotation->setRotationType(ShopRotationEnum::Daily);
        $shopRotation->setValidFrom(new DateTimeImmutable('midnight'));
        $shopRotation->setValidUntil(new DateTimeImmutable('tomorrow'));

        // In future will not just take 8 random items but will take base on quotes, meaning 1 sword, 1 helmet...
        for ($i = 0; $i < 8; $i++) {
            $itemDefinition = $this->itemDefinitionRepository->findRandomByLevel($character->getLevel());
            $offer = new ShopOffer($shopRotation, $itemDefinition);
            $offer->setGoldPrice($itemDefinition->getBaseGoldPrice() * (mt_rand(80, 120) / 100)); // Random price between 80% and 120% of base price
            $offer->setDiamondPrice($itemDefinition->getBaseDiamondPrice() * (mt_rand(80, 120) / 100));
            [$bonusDamage, $bonusCrit, $bonusHealth] = $this->itemFactory->rollBonusStats($itemDefinition);
            $offer->setBonusDamage($bonusDamage);
            $offer->setBonusCrit($bonusCrit);
            $offer->setBonusHealth($bonusHealth);
            $shopRotation->addShopOffer($offer);
        }

        $this->entityManager->persist($shopRotation);
        $this->entityManager->flush();

        return $shopRotation;
    }
}
