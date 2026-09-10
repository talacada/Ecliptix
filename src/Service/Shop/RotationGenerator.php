<?php

declare(strict_types=1);

namespace App\Service\Shop;

use App\Config\ItemConfig;
use App\Entity\Character\Character;
use App\Entity\Shop\ShopOffer;
use App\Entity\Shop\ShopRotation;
use App\Entity\Shop\ShopRotationEnum;
use App\Repository\Item\ItemDefinitionRepository;
use App\Repository\Shop\ShopRotationRepository;
use App\Service\Item\ItemStatCalculator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class RotationGenerator
{
    private const array OFFER_QUOTA = [
        'elixir' => 2,
        'equipment' => 8,
    ];

    public function __construct(
        private ItemDefinitionRepository $itemDefinitionRepository,
        private EntityManagerInterface $entityManager,
        private ShopRotationRepository $shopRotationRepository,
    ) {
    }

    public function generateDaily(Character $character): ShopRotation
    {
        $oldRotations = $this->shopRotationRepository->findAllExpired($character);
        if ($oldRotations !== []) {
            foreach ($oldRotations as $rotation) {
                $this->entityManager->remove($rotation);
            }
        }
        $shopRotation = new ShopRotation();
        $shopRotation->setCharacter($character);
        $shopRotation->setRotationType(ShopRotationEnum::Daily);
        $shopRotation->setValidFrom(new DateTimeImmutable('midnight'));
        $shopRotation->setValidUntil(new DateTimeImmutable('tomorrow'));

        for ($i = 0; $i < self::OFFER_QUOTA['elixir']; ++$i) {
            $elixirDef = $this->itemDefinitionRepository->findRandomElixir();
            if (null === $elixirDef) {
                continue;
            }
            $offer = new ShopOffer($shopRotation, $elixirDef);
            $offer->setGoldPrice((int) ($elixirDef->getBaseGoldPrice() * $character->getLevel() * (mt_rand(ItemConfig::SHOP_PRICE_VARIANCE_MIN, ItemConfig::SHOP_PRICE_VARIANCE_MAX) / 100)));
            $offer->setDiamondPrice($elixirDef->getBaseDiamondPrice());
            $offer->setBonusDamage(0);
            $offer->setBonusCrit(0);
            $offer->setBonusHealth(0);
            $shopRotation->addShopOffer($offer);
        }

        for ($i = 0; $i < self::OFFER_QUOTA['equipment']; ++$i) {
            $itemDefinition = $this->itemDefinitionRepository->findRandomByLevel($character->getLevel());
            if (null === $itemDefinition) {
                continue;
            }
            $offer = new ShopOffer($shopRotation, $itemDefinition);
            $offer->setGoldPrice((int) ($itemDefinition->getBaseGoldPrice() * $character->getLevel() * (mt_rand(ItemConfig::SHOP_PRICE_VARIANCE_MIN, ItemConfig::SHOP_PRICE_VARIANCE_MAX) / 100)));
            $offer->setDiamondPrice((int) ($itemDefinition->getBaseDiamondPrice() * (mt_rand(ItemConfig::SHOP_PRICE_VARIANCE_MIN, ItemConfig::SHOP_PRICE_VARIANCE_MAX) / 100)));
            [$bonusDamage, $bonusCrit, $bonusHealth] = ItemStatCalculator::rollBonusStats($itemDefinition);
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
