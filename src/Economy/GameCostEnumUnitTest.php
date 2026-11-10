<?php

declare(strict_types=1);

namespace App\Economy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GameCostEnumUnitTest extends TestCase
{
    #[DataProvider('costDataProvider')]
    public function testGameCostValues(GameCostEnum $cost, int $expectedAmount, CurrencyEnum $expectedCurrency): void
    {
        $this->assertSame($expectedAmount, $cost->getAmount());
        $this->assertSame($expectedCurrency, $cost->getCurrency());
    }

    /**
     * @return iterable<string, array{GameCostEnum, int, CurrencyEnum}>
     */
    public static function costDataProvider(): iterable
    {
        yield 'change appearance' => [
            GameCostEnum::CHANGE_APPEARANCE,
            5,
            CurrencyEnum::DIAMONDS,
        ];
    }
}
