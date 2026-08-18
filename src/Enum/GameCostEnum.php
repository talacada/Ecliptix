<?php

declare(strict_types=1);

namespace App\Enum;

enum GameCostEnum
{
    case CHANGE_APPEARANCE;

    public function getAmount(): int
    {
        return match ($this) {
            self::CHANGE_APPEARANCE => 5,
        };
    }

    public function getCurrency(): CurrencyEnum
    {
        return match ($this) {
            self::CHANGE_APPEARANCE => CurrencyEnum::DIAMONDS,
        };
    }
}
