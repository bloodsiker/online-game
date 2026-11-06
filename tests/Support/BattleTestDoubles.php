<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\AttackService;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Application\Services\Combat\MonsterAttackService;
use App\Modules\Battle\Application\Services\DropService;
use App\Modules\Battle\Application\Services\MonsterLootService;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;

final class BattleTestDoubles
{
    public static function bind(bool $escape = true): void
    {
        Event::fake();
        $random = Mockery::mock(RandomizerInterface::class);
        $random->shouldReceive('between')->andReturn($escape ? 100 : 0);
        app()->instance(RandomizerInterface::class, $random);
        $effects = Mockery::mock(BattleEffectService::class);
        $effects->shouldReceive('processPlayerEffects', 'processMonsterEffects')->andReturn(false);
        app()->instance(BattleEffectService::class, $effects);
        app()->instance(MonsterAttackService::class, new NoCounterattack);
        app()->instance(AttackService::class, new FixedDamageAttack);
        $drop = Mockery::mock(DropService::class);
        $drop->shouldReceive('dropItemsFromMonsters')->andReturnNull();
        app()->instance(DropService::class, $drop);
    }
}

readonly class NoCounterattack extends MonsterAttackService
{
    public function __construct() {}

    public function execute(Player $player, MonsterOnLocation $locationMonster, AttackResultDTO $result): void {}
}

readonly class FixedDamageAttack extends AttackService
{
    public function __construct() {}

    public function execute(Player $player, MonsterOnLocation $locMonster, int $action, Battle $battle, float $xpMultiplier = 1.0): AttackResultDTO
    {
        (new MonsterLootService)->damage($locMonster, 10, (int) $player->user_id, (int) $battle->id);

        return (new AttackResultDTO)->log('<p>Урон 10.</p>');
    }

    public function handleMonsterDeath(Player $player, MonsterOnLocation $locationMonster, BattleDetail $attackedMonster, AttackResultDTO $result)
    {
        $locationMonster->active = 0;
        $locationMonster->save();
        $attackedMonster->status = 0;
        $attackedMonster->save();
        DB::table('battle_test_kills')->insert(['spawn_id' => $locationMonster->id]);
    }

    public function checkLevelUp(Player $player, AttackResultDTO $result) {}
}
