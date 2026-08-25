<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Application\Services\Combat\MonsterOnHitEffectService;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Domain\Enums\EffectDamageScalingType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Pivot;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MonsterOnHitEffectServiceTest extends TestCase
{
    #[Test]
    public function assigned_bleed_is_applied_with_damage_scaled_tick_value(): void
    {
        $effectService = $this->createMock(BattleEffectService::class);
        $random = $this->createMock(RandomizerInterface::class);
        $service = new MonsterOnHitEffectService($effectService, $random);

        $player = new Player;
        $player->hp_now = 900;
        $monster = $this->monsterWithEffect(
            activeType: ActiveEffectType::BLEED,
            chance: 18,
            powerPercent: 7,
        );
        $hit = (new FightHitDTO)->setDamage(100);
        $result = new AttackResultDTO;

        $random->expects($this->once())->method('chance')->with(18.0)->willReturn(true);
        $effectService->expects($this->once())
            ->method('applyEffectToPlayer')
            ->with(
                $monster->effects->first(),
                $player,
                null,
                $result,
                3,
                7,
            );

        $service->apply($player, $monster, $hit, $result);

        $this->assertStringContainsString('Кровотечение', $result->getLog());
        $this->assertStringNotContainsString('урона раз в', $result->getLog());
    }

    #[Test]
    public function dodge_or_zero_damage_never_rolls_or_applies_an_effect(): void
    {
        $effectService = $this->createMock(BattleEffectService::class);
        $random = $this->createMock(RandomizerInterface::class);
        $service = new MonsterOnHitEffectService($effectService, $random);

        $player = new Player;
        $player->hp_now = 100;
        $monster = $this->monsterWithEffect(ActiveEffectType::BLEED, 100, 7);
        $result = new AttackResultDTO;

        $random->expects($this->never())->method('chance');
        $effectService->expects($this->never())->method('applyEffectToPlayer');

        $service->apply($player, $monster, (new FightHitDTO)->setDamage(0), $result);
        $service->apply($player, $monster, (new FightHitDTO)->setDamage(10)->setDodge(true), $result);
    }

    #[Test]
    public function target_max_hp_scaling_distributes_total_percent_over_the_whole_duration(): void
    {
        $effectService = $this->createMock(BattleEffectService::class);
        $random = $this->createMock(RandomizerInterface::class);
        $service = new MonsterOnHitEffectService($effectService, $random);

        $player = new Player;
        $player->hp_now = 800;
        $player->hp_max = 1_000;
        $monster = $this->monsterWithEffect(
            activeType: ActiveEffectType::BLEED,
            chance: 100,
            powerPercent: 7,
            scalingType: EffectDamageScalingType::TARGET_MAX_HP,
            durationSeconds: 20,
        );
        $hit = (new FightHitDTO)->setDamage(2);
        $result = new AttackResultDTO;

        $random->method('chance')->willReturn(true);
        $effectService->expects($this->once())
            ->method('applyEffectToPlayer')
            ->with($monster->effects->first(), $player, null, $result, 20, 3.5);

        $service->apply($player, $monster, $hit, $result, 1_000);

        $this->assertStringContainsString(
            'После удара Кровавый волк на вас наложен эффект <b>Кровотечение</b>',
            $result->getLog(),
        );
        $this->assertStringNotContainsString('7% от максимального HP', $result->getLog());
    }

    #[Test]
    public function stat_debuff_is_applied_without_a_dot_tick_override(): void
    {
        $effectService = $this->createMock(BattleEffectService::class);
        $random = $this->createMock(RandomizerInterface::class);
        $service = new MonsterOnHitEffectService($effectService, $random);

        $player = new Player;
        $player->hp_now = 100;
        $monster = $this->monsterWithEffect(null, 100, null, 'Разрыв брони');
        $hit = (new FightHitDTO)->setDamage(50);
        $result = new AttackResultDTO;

        $random->method('chance')->willReturn(true);
        $effectService->expects($this->once())
            ->method('applyEffectToPlayer')
            ->with($monster->effects->first(), $player, null, $result, 3, null);

        $service->apply($player, $monster, $hit, $result);

        $this->assertStringContainsString('Разрыв брони', $result->getLog());
    }

    #[Test]
    public function timed_control_can_be_applied_after_a_successful_hit(): void
    {
        $effectService = $this->createMock(BattleEffectService::class);
        $random = $this->createMock(RandomizerInterface::class);
        $service = new MonsterOnHitEffectService($effectService, $random);

        $player = new Player;
        $player->hp_now = 100;
        $monster = $this->monsterWithEffect(ActiveEffectType::PARALYSIS, 100, null, 'Паралич');
        $hit = (new FightHitDTO)->setDamage(25);
        $result = new AttackResultDTO;

        $random->method('chance')->willReturn(true);
        $effectService->expects($this->once())
            ->method('applyEffectToPlayer')
            ->with($monster->effects->first(), $player, null, $result, 3, null);

        $service->apply($player, $monster, $hit, $result);

        $this->assertStringContainsString('Паралич', $result->getLog());
        $this->assertStringNotContainsString('сек.', $result->getLog());
    }

    private function monsterWithEffect(
        ?ActiveEffectType $activeType,
        float $chance,
        ?float $powerPercent,
        string $name = 'Кровотечение',
        EffectDamageScalingType $scalingType = EffectDamageScalingType::HIT_DAMAGE,
        int $durationSeconds = 3,
    ): Monster {
        $effect = new Effect;
        $effect->forceFill([
            'name' => $name,
            'slug' => 'monster_test_effect',
            'type' => 'debuff',
            'active_type' => $activeType,
            'damage_scaling_type' => $scalingType,
            'tick_interval' => 1,
            'value_per_tick' => 0,
            'stat_modifiers' => $activeType === null
                ? [['type' => 'armor', 'value' => -12, 'is_percent' => true]]
                : null,
        ]);

        $pivot = new Pivot;
        $pivot->forceFill([
            'chance' => $chance,
            'duration_seconds' => $durationSeconds,
            'power_percent' => $powerPercent,
            'trigger_on_hit' => true,
        ]);
        $effect->setRelation('pivot', $pivot);

        $monster = new Monster;
        $monster->name = 'Кровавый волк';
        $monster->setRelation('effects', new Collection([$effect]));

        return $monster;
    }
}
