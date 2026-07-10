<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Item\ElixirDefinition;
use App\Entity\Item\ElixirTypeEnum;
use App\Entity\Item\ItemRarityEnum;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ElixirDefinition>
 */
final class ElixirDefinitionFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return ElixirDefinition::class;
    }

    public const array VARIANTS = [
        ['bonus' => 10, 'days' => 3],
        ['bonus' => 20, 'days' => 3],
        ['bonus' => 25, 'days' => 6],
    ];

    private const int COST_FACTOR = 50;

    protected function defaults(): array
    {
        $type = self::faker()->randomElement(ElixirTypeEnum::cases());
        /** @var array{days: int, bonus: int} $variant */
        $variant = self::faker()->randomElement(self::VARIANTS);

        assert($type instanceof ElixirTypeEnum);

        $durationSeconds = $variant['days'] * 86400;
        $price = self::calculatePrice($variant['bonus'], $durationSeconds);

        return [
            'elixirType' => $type,
            'percentageBonus' => $variant['bonus'],
            'durationSeconds' => $durationSeconds,
            'baseGoldPrice' => $price,
            'baseDiamondPrice' => 0,
            'rarity' => ItemRarityEnum::Common,
            'name' => sprintf('%s elixir +%d%%', ucfirst($type->value), $variant['bonus']),
            'description' => sprintf('+%d%% %s for %d days', $variant['bonus'], $type->value, $variant['days']),
        ];
    }

    public static function calculatePrice(int $percentageBonus, int $durationSeconds): int
    {
        $days = $durationSeconds / 86400;

        return (int) round($percentageBonus * $days * self::COST_FACTOR);
    }
}
