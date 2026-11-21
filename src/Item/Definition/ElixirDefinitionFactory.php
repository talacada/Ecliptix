<?php

declare(strict_types=1);

namespace App\Item\Definition;

use App\Item\Stats\ElixirStatsConfig;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ElixirDefinition>
 */
final class ElixirDefinitionFactory extends PersistentObjectFactory
{
    private const int SECONDS_PER_DAY = 86400;

    public static function class(): string
    {
        return ElixirDefinition::class;
    }

    /**
     * Only the effect (type, bonus, duration) is generated here.
     * Name, description and price are derived from it in initialize(),
     * so they stay consistent even when the effect is overridden via with().
     */
    protected function defaults(): array
    {
        $type = self::faker()->randomElement(ElixirTypeEnum::cases());
        /** @var array{days: int, bonus: int, name: string} $variant */
        $variant = self::faker()->randomElement(ElixirStatsConfig::VARIANTS);

        assert($type instanceof ElixirTypeEnum);

        return [
            'elixirType' => $type,
            'percentageBonus' => $variant['bonus'],
            'durationSeconds' => $variant['days'] * self::SECONDS_PER_DAY,
            'baseDiamondPrice' => 0,
            'rarity' => ItemRarityEnum::Common,
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate(static function (ElixirDefinition $elixir): void {
            $type = $elixir->getElixirType();
            $bonus = $elixir->getPercentageBonus();
            $duration = $elixir->getDurationSeconds();
            $days = (int) round($duration / self::SECONDS_PER_DAY);

            $variantName = null;
            foreach (ElixirStatsConfig::VARIANTS as $variant) {
                if ($variant['bonus'] === $bonus && $variant['days'] * self::SECONDS_PER_DAY === $duration) {
                    $variantName = $variant['name'];
                    break;
                }
            }

            $elixir
                ->setName(sprintf('%s %s potion', $variantName ?? sprintf('+%d%%', $bonus), $type->value))
                ->setDescription(sprintf('Increases %s by %d%% for %d days', $type->value, $bonus, $days))
                ->setBaseGoldPrice(self::calculatePrice($bonus, $duration));
        });
    }

    public static function calculatePrice(int $percentageBonus, int $durationSeconds): int
    {
        $days = $durationSeconds / self::SECONDS_PER_DAY;

        return (int) round($percentageBonus * $days * ElixirStatsConfig::COST_FACTOR);
    }
}
