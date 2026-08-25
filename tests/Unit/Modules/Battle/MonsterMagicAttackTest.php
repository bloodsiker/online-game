<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Application\Services\Combat\Boss\BossPhaseService;
use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Battle\Application\Services\Combat\MonsterAttackService;
use App\Modules\Battle\Application\Services\Combat\MonsterOnHitEffectService;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Monster\Domain\Enums\MonsterAttackType;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Domain\Services\PlayerRunePassiveService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Services\PlayerSkillService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MonsterMagicAttackTest extends TestCase
{
    #[Test]
    public function magic_monster_attack_uses_magic_resistance_not_physical_hit_calculator(): void
    {
        $physicalCalculator = $this->createMock(HitCalculator::class);
        $physicalCalculator->expects($this->never())->method('hit');

        $service = new MonsterAttackService(
            hitCalc: $physicalCalculator,
            magicHitCalc: new MagicHitCalculator,
            bossPhaseService: $this->createMock(BossPhaseService::class),
            effectService: $this->createMock(BattleEffectService::class),
            onHitEffectService: new MonsterOnHitEffectService(
                $this->createMock(BattleEffectService::class),
                $this->createMock(RandomizerInterface::class),
            ),
            statService: $this->createMock(PlayerStatService::class),
            runePassiveService: $this->createMock(PlayerRunePassiveService::class),
            random: $this->createMock(RandomizerInterface::class),
            playerSkillService: $this->createMock(PlayerSkillService::class),
        );

        $monster = new Monster;
        $monster->attack_type = MonsterAttackType::MAGIC;
        $monster->lvl = 12;
        $monster->magic_attack = 10;

        $target = new StatSheet;
        $target->level = 12;
        $target->magicResistance = 220;

        $method = new \ReflectionMethod($service, 'resolveHit');
        /** @var FightHitDTO $hit */
        $hit = $method->invoke($service, $monster, $target, 100, 100, true, 1.0);

        // raw = 100 + 10; при резисте 220 и A=220 проходит ровно половина.
        $this->assertSame(55, $hit->getDamage());
    }
}
