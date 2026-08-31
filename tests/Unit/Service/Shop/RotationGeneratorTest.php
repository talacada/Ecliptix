<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Shop;

use PHPUnit\Framework\TestCase;

class RotationGeneratorTest extends TestCase
{
    // TODO: testGenerateDailyRotationCleansUpOldExpiredRotations() - mock ShopRotationRepository::findAllExpired() and assert EntityManager::remove() is called for each
    // TODO: testGenerateCreatesDailyRotationWithCorrectDateRange() - assert rotationType is Daily, validFrom is midnight, validUntil is tomorrow
    // TODO: testGenerateCreatesExactQuotaOfOffers() - assert creates 2 elixir offers and 8 equipment offers linked to the rotation
    // TODO: testGenerateCalculatesPricesAndBonusStatsCorrectly() - assert gold/diamond prices and bonus stats match item definition & level calculations
    // TODO: testGenerateSkipsOfferWhenRepositoryReturnsNull() - assert continues without error if findRandomElixir or findRandomByLevel returns null
}
