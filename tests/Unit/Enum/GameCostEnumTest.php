<?php

declare(strict_types=1);

namespace App\Tests\Unit\Enum;

use App\Enum\CurrencyEnum;
use App\Enum\GameCostEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class GameCostEnumTest extends TestCase
{
    #[DataProvider('costDataProvider')]
    public function testGameCostValues(GameCostEnum $cost, int $expectedAmount, CurrencyEnum $expectedCurrency): void
    {
        $this->assertSame($expectedAmount, $cost->getAmount());
        $this->assertSame($expectedCurrency, $cost->getCurrency());
    }

    public static function costDataProvider(): iterable
    {
        yield 'change appearance' => [
            GameCostEnum::CHANGE_APPEARANCE,
            5,
            CurrencyEnum::DIAMONDS,
        ];
    }
}
