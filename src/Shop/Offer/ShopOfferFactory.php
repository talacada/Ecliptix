<?php

declare(strict_types=1);

namespace App\Shop\Offer;

use App\Item\Definition\ItemDefinitionFactory;
use App\Shop\Rotation\ShopRotationFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ShopOffer>
 */
class ShopOfferFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ShopOffer::class;
    }

    protected function defaults(): array
    {
        return [
            'rotation' => ShopRotationFactory::createOne(),
            'ItemDefinition' => ItemDefinitionFactory::createOne(),
            'bonusDamage' => 0,
            'bonusCrit' => 0,
            'bonusHealth' => 0,
        ];
    }
}
