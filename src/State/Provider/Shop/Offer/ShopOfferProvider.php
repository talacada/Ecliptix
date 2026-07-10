<?php

declare(strict_types=1);

namespace App\State\Provider\Shop\Offer;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Shop\ShopOffer;
use App\Repository\Shop\ShopOfferRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<ShopOffer>
 */
class ShopOfferProvider implements ProviderInterface
{
    private ShopOfferRepository $shopOfferRepository;

    public function __construct(
        ShopOfferRepository $shopOfferRepository,
    ) {
        $this->shopOfferRepository = $shopOfferRepository;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ShopOffer
    {
        $offer = $this->shopOfferRepository->getById($uriVariables['id']);

        if (null === $offer) {
            throw new NotFoundHttpException('Shop offer not found');
        }

        return $offer;
    }
}
