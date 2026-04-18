<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\DTO\AttackResultDTO;
use App\Modules\MagicSkill\Application\DTOs\MagicSkillActionResultDTO;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillReadRepository;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillWriteRepository;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\Combat\BattleEffectService;

class UseMagicSkill
{
    public function __construct(
        private readonly MagicSkillReadRepository $readRepository,
        private readonly MagicSkillWriteRepository $writeRepository,
        private readonly PlayerStatService $statService,
        private readonly BattleEffectService $effectService,
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

        $pivot = $skill->pivot;
        if ($pivot?->cooldown_end_at && now()->lt($pivot->cooldown_end_at)) {
            $remaining = (int) now()->diffInSeconds($pivot->cooldown_end_at, false);

            return new MagicSkillActionResultDTO(
                'error',
                sprintf('Заклинание на перезарядке ещё %d сек.', $remaining),
                httpCode: 422,
            );
        }

        if ($caster->mp_now < $skill->mana_cost) {
            return new MagicSkillActionResultDTO(
                'error',
                sprintf('Недостаточно маны. Нужно: %d MP', $skill->mana_cost),
                httpCode: 422,
            );
        }

        $target = $this->readRepository->findAllyTarget($caster, $targetPlayerId);
        if (! $target) {
            return new MagicSkillActionResultDTO('error', 'Цель не найдена', httpCode: 404);
        }

        $casterSheet = $this->statService->resolve($caster);
        $targetSheet = $target->id === $caster->id ? $casterSheet : $this->statService->resolve($target);

        $this->writeRepository->consumeMana($caster, $skill->mana_cost);

        $log = new AttackResultDTO;

        if ($skill->base_healing > 0) {
            $heal = $skill->base_healing;
            $target->hp_now = min($targetSheet->getHpMax(), $target->hp_now + $heal);
            $log->log(sprintf('Заклинание восстановило <b>%d HP</b> игроку %s', $heal, $target->user->name));
        }

        $appliedBlessings = [];
        $skill->loadMissing('skillEffects');
        foreach ($skill->skillEffects as $effect) {
            if (random_int(1, 100) <= $effect->pivot->chance) {
                $this->effectService->applyEffectToPlayer($effect, $target, null, $log);
                if ($target->id === $caster->id) {
                    $appliedBlessings[] = [
                        'id' => $effect->slug.'_'.time(),
                        'name' => $effect->name,
                        'duration' => (int) $effect->duration,
                    ];
                }
            }
        }

        $this->writeRepository->savePlayers($caster, $target);

        $cooldownEndsAt = $skill->cooldown > 0 ? now()->addSeconds($skill->cooldown) : null;
        $this->writeRepository->updateCooldown($caster, $skill, $cooldownEndsAt);

        $freshCaster = $caster->fresh();
        $freshSheet = $this->statService->resolve($freshCaster);

        return new MagicSkillActionResultDTO(
            status: 'success',
            message: $log->getLog() ?: sprintf('Применено: «%s»', $skill->name),
            hp: ['current' => $freshCaster->hp_now, 'max' => $freshSheet->getHpMax()],
            mp: ['current' => $freshCaster->mp_now, 'max' => $freshSheet->getMpMax()],
            cooldownUntil: $cooldownEndsAt?->getTimestamp(),
            blessings: $appliedBlessings,
        );
    }
}
