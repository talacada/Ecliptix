<?php

declare(strict_types=1);

namespace App\Shop\Offer;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Support\Exception\ResourceNotFoundException;

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
            throw new ResourceNotFoundException(ShopOffer::class, $uriVariables['id']);
        }

        return $offer;
    }
}
