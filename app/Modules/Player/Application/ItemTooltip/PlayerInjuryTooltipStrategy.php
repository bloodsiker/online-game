<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\ItemTooltip;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipDto;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemTooltipStrategyInterface;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerInjury;

final readonly class PlayerInjuryTooltipStrategy implements ItemTooltipStrategyInterface
{
    /**
     * @param  iterable<int|string, PlayerInjury>  $injuries
     */
    public function __construct(private iterable $injuries) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        foreach ($this->injuries as $injury) {
            $injury->loadMissing('injuryType');

            $collector->add(new ItemTooltipDto(
                id: $injury->tooltipId(),
                title: e($injury->displayName()),
                color: $this->titleColor($injury),
                image: $injury->imageUrl() ?? asset('img/bg/empty_slot.gif'),
                kind: 'Травма: '.e($injury->body_part->label()),
                price: '',
                diamond: 'Действует ещё: '.$injury->remainingLabel(),
                lev: [],
                skills: [],
                desc: nl2br(e($injury->injuryType?->description ?? 'До окончания травмы этот слот экипировки заблокирован.')),
                store: false,
                nogive: false,
                noweight: false,
                nosell: false,
                stats: $this->statRows($injury),
            ));
        }
    }

    /** @return list<array{title: string, value: string}> */
    private function statRows(PlayerInjury $injury): array
    {
        $rows = [[
            'title' => 'Уровень травмы',
            'value' => e($injury->severity->label()),
        ]];

        foreach ($injury->statModifiers() as $modifier) {
            $value = (float) ($modifier['value'] ?? 0);
            $formatted = rtrim(rtrim(number_format(abs($value), 2, '.', ''), '0'), '.');
            $unit = ! empty($modifier['is_percent']) ? '%' : '';
            $stat = PlayerStatKey::tryFrom((string) ($modifier['stat'] ?? ''))?->label()
                ?? (string) ($modifier['stat'] ?? 'Характеристика');

            $rows[] = [
                'title' => e($stat),
                'value' => ($value >= 0 ? '+' : '−').$formatted.$unit,
            ];
        }

        return $rows;
    }

    private function titleColor(PlayerInjury $injury): string
    {
        return match ($injury->severity->value) {
            1 => '#a55218',
            2 => '#c13c14',
            default => '#a40000',
        };
    }
}
