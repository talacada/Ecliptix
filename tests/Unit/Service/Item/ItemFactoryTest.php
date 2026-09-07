<?php

namespace App\Tests\Unit\Service\Item;


use App\Entity\Item\ItemDefinition;
use App\Entity\Shop\ShopOffer;
use App\Entity\Shop\ShopRotation;
use App\Service\Item\ItemFactory;
use PHPUnit\Framework\TestCase;

class ItemFactoryTest extends TestCase
{
    public function testCreateFromDefinitionAndOfferSetsDefinitionAndBonusStats(): void
    {
        $definition = new ItemDefinition();

        $bonusHealth = 11;
        $bonusCrit = 23;
        $bonusDamage = 34;

        $offer = new ShopOffer(new ShopRotation(), $definition);
        $offer->setBonusHealth($bonusHealth);
        $offer->setBonusCrit($bonusCrit);
        $offer->setBonusDamage($bonusDamage);

        $factory = new ItemFactory();
        $item = $factory->createFromDefinitionAndOffer($offer);

        $this->assertSame($definition, $item->getDefinition());
        $this->assertSame($bonusHealth, $item->getBonusHealth());
        $this->assertSame($bonusCrit, $item->getBonusCrit());
        $this->assertSame($bonusDamage, $item->getBonusDamage());
    }

    public function testCreateFromDefinitionAndOfferFallsBackToZeroForNullBonusStats(): void
    {
        $definition = new ItemDefinition();

        $offer = new ShopOffer(new ShopRotation(), $definition);

        $factory = new ItemFactory();
        $item = $factory->createFromDefinitionAndOffer($offer);

        $this->assertSame($definition, $item->getDefinition());
        $this->assertSame(0, $item->getBonusHealth());
        $this->assertSame(0, $item->getBonusCrit());
        $this->assertSame(0, $item->getBonusDamage());
    }
}
