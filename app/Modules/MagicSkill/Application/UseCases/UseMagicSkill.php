<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Effect\Application\DTOs\PlayerEffectNotificationDTO;
use App\Modules\MagicSkill\Application\DTOs\MagicSkillActionResultDTO;
use App\Modules\MagicSkill\Application\Services\MagicCastGuard;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillReadRepository;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillWriteRepository;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UseMagicSkill
{
    public function __construct(
        private readonly MagicSkillReadRepository $readRepository,
        private readonly MagicSkillWriteRepository $writeRepository,
        private readonly PlayerStatService $statService,
        private readonly BattleEffectService $effectService,
        private readonly MagicCastGuard $castGuard,
        private readonly MagicHitCalculator $magicHitCalc,
    ) {}

    public function execute(User $user, int $skillId, ?int $targetPlayerId): MagicSkillActionResultDTO
    {
        $caster = $user->player;
        $skill = $this->readRepository->findOwnedSkill($caster, $skillId);

        if (! $skill) {
            return new MagicSkillActionResultDTO('error', 'Заклинание не изучено', httpCode: 403);
        }

        if (! $skill->isBuffSkill()) {
            return new MagicSkillActionResultDTO('error', 'Это заклинание нельзя использовать вне боя', httpCode: 422);
        }

        $target = $this->readRepository->findAllyTarget($caster, $targetPlayerId);
        if (! $target) {
            return new MagicSkillActionResultDTO('error', 'Цель не найдена', httpCode: 404);
        }

        $castAttempt = $this->castGuard->tryConsume($caster, $skill);

        if (! $castAttempt->ok) {
            return new MagicSkillActionResultDTO('error', $castAttempt->reason, httpCode: 422);
        }

        $casterSheet = $this->statService->resolve($caster);
        $targetSheet = $target->id === $caster->id ? $casterSheet : $this->statService->resolve($target);

        $log = new AttackResultDTO;

        if ($skill->base_healing > 0) {
            $heal = $this->magicHitCalc->heal(
                $casterSheet,
                minHeal: $skill->base_healing,
                maxHeal: $skill->base_healing,
                powerCoefficient: $skill->power_coefficient,
            );
            $target->hp_now = min($targetSheet->getHpMax(), $target->hp_now + $heal);
            $log->log(sprintf('Заклинание восстановило <b>%d HP</b> игроку %s', $heal, $target->user->name));
        }

        $skill->loadMissing('skillEffects');
        foreach ($skill->skillEffects as $effect) {
            if (random_int(1, 100) <= $effect->pivot->chance) {
                $durationSeconds = (int) $effect->pivot->duration_seconds;
                $this->effectService->applyEffectToPlayer($effect, $target, null, $log, $durationSeconds);
            }
        }

        $appliedBlessings = $target->id === $caster->id
            ? array_map(
                static fn (PlayerEffectNotificationDTO $effect): array => $effect->toArray(),
                $log->getPlayerEffects(),
            )
            : [];

        $this->writeRepository->savePlayers($caster, $target);

        $freshCaster = $caster->fresh();
        $freshSheet = $this->statService->resolve($freshCaster);

        $cooldownEndAt = DB::table('player_magic_skills')
            ->where('player_id', $caster->id)->where('magic_skill_id', $skill->id)
            ->value('cooldown_end_at');

        return new MagicSkillActionResultDTO(
            status: 'success',
            message: $log->getLog() ?: sprintf('Применено: «%s»', $skill->name),
            hp: ['current' => $freshCaster->hp_now, 'max' => $freshSheet->getHpMax()],
            mp: ['current' => $freshCaster->mp_now, 'max' => $freshSheet->getMpMax()],
            cooldownUntil: $cooldownEndAt ? Carbon::parse($cooldownEndAt)->getTimestamp() : null,
            blessings: $appliedBlessings,
        );
    }
}
