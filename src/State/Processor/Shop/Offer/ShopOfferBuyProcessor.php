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

        //TODO validate if has permissions for this, get the rotation. Check if not expired or belongs to him

        //TODO check if has money available
        //TODO check if has backspace space available

        dd($data->getRotation()->getValidFrom(), $data);
    }
}
