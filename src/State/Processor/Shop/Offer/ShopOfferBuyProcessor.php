<?php

namespace App\State\Processor\Shop\Offer;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Shop\ShopOffer;

class ShopOfferBuyProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        assert($data instanceof ShopOffer);

        dd($data);
    }
}
