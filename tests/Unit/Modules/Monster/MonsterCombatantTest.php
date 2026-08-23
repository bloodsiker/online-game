<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Monster;

use App\Modules\Monster\Domain\DTO\MonsterCombatant;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use PHPUnit\Framework\TestCase;

class MonsterCombatantTest extends TestCase
{
    public function test_debuff_reduces_armor_but_not_below_zero(): void
    {
        $monster = new Monster(['armor' => 10, 'dodge' => 5, 'critical' => 0, 'lvl' => 20]);
        $combatant = new MonsterCombatant($monster, ['armor' => -15.0, 'dodge' => -2.0]);

        $this->assertSame(0, $combatant->getArmor(), 'armor must clamp at 0, not go negative');
        $this->assertSame(3, $combatant->getDodge());
    }

    public function test_no_debuffs_passes_through_species_stats_unchanged(): void
    {
        $monster = new Monster(['armor' => 40, 'dodge' => 12, 'critical' => 8, 'lvl' => 30]);
        $combatant = new MonsterCombatant($monster, []);

        $this->assertSame(40, $combatant->getArmor());
        $this->assertSame(12, $combatant->getDodge());
        $this->assertSame(8, $combatant->getCritical());
        $this->assertSame(30, $combatant->getLevel());
    }
}
