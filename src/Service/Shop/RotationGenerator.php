<?php

use App\Entity\Character\Character;
use App\Entity\Shop\ShopRotation;
use App\Entity\Shop\ShopRotationEnum;

class RotationGenerator {
    public function generate(Character $character)
    {
        $newShopRotation = new ShopRotation();
        $newShopRotation->setCharacter($character);
        $newShopRotation->setRotationType(ShopRotationEnum::Daily);
        $newShopRotation->setValidFrom(new DateTimeImmutable('midnight'));
        $newShopRotation->setValidUntil(new DateTimeImmutable('tomorrow'));
        //TODO pokracovat tady
    }
}
