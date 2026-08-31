<?php

namespace App\Tests\Unit\Service\Item;


use PHPUnit\Framework\TestCase;

class ItemFactoryTest extends TestCase
{
    // TODO: testCreateFromDefinitionAndOfferSetsDefinitionAndBonusStats() - assert Item is created with given ItemDefinition and exact bonus damage, crit, and health from ShopOffer
    // TODO: testCreateFromDefinitionAndOfferFallsBackToZeroForNullBonusStats() - assert null bonus values in ShopOffer default to 0 on created Item
}
