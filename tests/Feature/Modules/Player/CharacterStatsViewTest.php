<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Player\Application\DTOs\CharacterDTO;
use App\Modules\Player\Domain\DTO\StatSheet;
use Tests\TestCase;

class CharacterStatsViewTest extends TestCase
{
    public function test_character_page_displays_all_resolved_combat_stats(): void
    {
        $stats = new StatSheet;
        $stats->hpMax = 100;
        $stats->mpMax = 80;
        $stats->critDamage = 225;
        $stats->blockChance = 27;
        $stats->blockFlat = 14;
        $stats->blockPercent = 18;
        $stats->magicAttack = 31;
        $stats->magicResistance = 42;
        $stats->magicCritical = 16;

        $character = new CharacterDTO(
            playerName: 'Тестовый герой',
            level: 10,
            raceName: 'Человек',
            hpNow: 90,
            mpNow: 70,
            money: 0,
            diamond: 0,
            bankBalance: 0,
            bankAccount: null,
            exp: 0,
            expUp: 100,
            expPercent: 0,
            victory: 0,
            death: 0,
            freeStats: 0,
            baseStrength: 1,
            baseIntuition: 1,
            baseAgility: 1,
            baseIntelligence: 1,
            baseWisdom: 1,
            baseEndurance: 1,
            stats: $stats,
            skills: [],
            weaponDamage: ['min' => 5, 'max' => 10],
        );

        $this->view('player::index', ['character' => $character, 'group' => 'character'])
            ->assertSee('Сила крит. удара')
            ->assertSee('225%')
            ->assertSee('Шанс блока щитом')
            ->assertSee('27%')
            ->assertSee('Блок щитом (фикс.)')
            ->assertSee('Блок щитом')
            ->assertSee('18%')
            ->assertSee('Магическая атака')
            ->assertSee('Магическое сопротивление')
            ->assertSee('Магический крит')
            ->assertSee('16%');
    }
}
