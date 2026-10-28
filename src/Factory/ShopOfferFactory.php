<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Shop\ShopOffer;
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
