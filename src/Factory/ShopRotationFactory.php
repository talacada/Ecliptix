<?php

namespace App\Factory;

use App\Entity\Shop\ShopRotation;
use App\Entity\Shop\ShopRotationEnum;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

class ShopRotationFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ShopRotation::class;
    }

    protected function defaults(): array
    {
        return [
            'validFrom' => new DateTimeImmutable('now - 1hours'),
            'validUntil' => new DateTimeImmutable('now + 1hours'),
            'character' => CharacterFactory::createOne(),
            'rotationType' => ShopRotationEnum::Daily,
        ];
    }
}
