<?php

namespace App\State\Provider\Shop\Offer;


use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Shop\ShopOffer;

class ShopOfferProvider implements ProviderInterface
{

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?ShopOffer
    {
        //TODO here get offer by id and return 404 or cant access by this user, than its passed to the processor only if its available
       dd($operation, $uriVariables, $context);
    }
}
