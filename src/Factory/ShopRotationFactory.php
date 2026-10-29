<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Shop\ShopRotation;
use App\Entity\Shop\ShopRotationEnum;
use DateMalformedStringException;
use DateTimeImmutable;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ShopRotation>
 */
class ShopRotationFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ShopRotation::class;
    }

    /**
     * @throws DateMalformedStringException
     */
    protected function defaults(): array
    {
        return [
            'validFrom' => new DateTimeImmutable('today'),
            'validUntil' => new DateTimeImmutable('tomorrow'),
            'character' => CharacterFactory::new(),
            'rotationType' => ShopRotationEnum::Daily,
        ];
    }
}
