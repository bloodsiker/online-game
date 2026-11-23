<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\Services\MonsterLootService;
use App\Modules\Battle\Application\Services\TrainingDummyService;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Application\Services\FatigueService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TrainingDummyTest extends TestCase
{
    private function spawn(bool $dummy, int $hp = 1): MonsterOnLocation
    {
        $location = new MonsterOnLocation(['hp_now' => $hp, 'hp_max' => $hp]);
        $location->setRelation('monster', new Monster(['is_training_dummy' => $dummy]));

        return $location;
    }

    public function test_dummy_never_drops_below_one_hp_and_writes_no_loot_damage(): void
    {
        $dummy = $this->spawn(true);

        // userId задан: у обычного монстра здесь была бы запись в battle_monster_damage (таблицы нет → упало бы).
        (new MonsterLootService)->damage($dummy, 9999, 5, 10);

        $this->assertSame(1, $dummy->hp_now);
    }

    public function test_regular_monster_still_dies(): void
    {
        $monster = $this->spawn(false, 10);

        (new MonsterLootService)->damage($monster, 9999, null);

        $this->assertSame(0, $monster->hp_now);
    }

    public function test_summary_shows_running_average_and_fatigue(): void
    {
        config()->set('fatigue.costs.training_dummy', 1);
        Cache::flush();
        $service = new TrainingDummyService(new FatigueService);
        $battle = new Battle;
        $battle->id = 77;
        $player = new Player;
        $player->id = 5;

        $service->recordHit($battle, $player, 10, false);
        $service->recordHit($battle, $player, 30, true);
        $service->recordMiss($battle, $player);
        $line = $service->summaryTable($battle, $player);

        $this->assertStringContainsString('>3</td>', $line);      // ударов (с промахом)
        $this->assertStringContainsString('>40</td>', $line);     // сумма
        $this->assertStringContainsString('>20</td>', $line);     // средний по попаданиям
        $this->assertStringContainsString('>10 / 30</td>', $line);  // мин/макс
        $this->assertStringContainsString('>50%</td>', $line);    // крит
        $this->assertStringContainsString('<b>3/100</b>', $line);  // усталость: по 1 за каждое действие
    }

    public function test_summary_counts_player_dodges(): void
    {
        Cache::flush();
        $service = new TrainingDummyService(new FatigueService);
        $battle = new Battle;
        $battle->id = 78;
        $player = new Player;
        $player->id = 6;

        $service->recordHit($battle, $player, 10, false);
        $service->recordDodge($battle, $player);
        $service->recordDodge($battle, $player);
        $line = $service->summaryTable($battle, $player);

        $this->assertStringContainsString('Увороты', $line);
        $this->assertMatchesRegularExpression('/Увороты<\/td><td[^>]*>2<\/td>/u', $line);
    }
}
