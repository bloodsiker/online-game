<?php

declare(strict_types=1);

namespace App\Modules\Monster\Domain\Services;

use App\Modules\Monster\Domain\DTO\MonsterCombatant;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterActiveEffect;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;

class MonsterCombatantFactory
{
    /** @var list<string> статы, которые дебафф умеет снижать в этом релизе — см. спеку */
    private const DEBUFFABLE_STATS = ['armor', 'dodge'];

    public function build(MonsterOnLocation $locMonster): MonsterCombatant
    {
        $totals = array_fill_keys(self::DEBUFFABLE_STATS, 0.0);

        $activeEffects = MonsterActiveEffect::query()
            ->where('location_monster_id', $locMonster->id)
            ->with('effect')
            ->get();

        foreach ($activeEffects as $active) {
            foreach ((array) ($active->effect?->stat_modifiers ?? []) as $modifier) {
                if (! is_array($modifier)) {
                    continue;
                }

                $type = $modifier['type'] ?? null;

                if (is_string($type) && array_key_exists($type, $totals) && ! ($modifier['is_percent'] ?? false)) {
                    $totals[$type] += (float) ($modifier['value'] ?? 0);
                }
            }
        }

        return new MonsterCombatant($locMonster->monster, $totals);
    }
}
