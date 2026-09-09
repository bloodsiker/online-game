<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Player\Domain\Events\PlayerDied;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Player\Domain\Services\PlayerInjuryService;
use App\Modules\Player\Domain\Services\PlayerRevivalService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;

final readonly class PlayerDeathFinalizer
{
    public function __construct(
        private BattleFinishService $battleFinishService,
        private DungeonCoordinator $dungeonCoordinator,
        private ExperienceService $experienceService,
        private PlayerInjuryService $injuryService,
        private PlayerStatService $statService,
        private BattleEffectService $battleEffectService,
    ) {}

    /**
     * Applies the common, persistent consequences of death exactly once for a
     * battle participant. The caller is responsible for locking the player and
     * wrapping this call in a transaction.
     */
    public function finalize(
        Player $player,
        ?BattleDetail $participant,
        AttackResultDTO $result,
        ?Battle $battle = null,
    ): bool {
        if ($participant !== null) {
            if ($participant->status->isDeath()) {
                return false;
            }

            // Mark the participant first. A concurrent heartbeat cannot apply
            // the experience penalty twice; a failed transaction rolls it back.
            $participant->status = 0;
            $participant->save();
        }

        if ($battle !== null) {
            $this->battleFinishService->finishIfNoLivingPlayers($battle);
        }

        $injury = $this->injuryService->inflictAfterDeath($player);
        if ($injury !== null) {
            $result->log(sprintf(
                '<p><b class="color-red">Получена травма: %s.</b> Наложен бинт на %d мин.</p>',
                e($injury->displayName()),
                max(1, (int) ceil(now()->diffInSeconds($injury->expires_at, false) / 60)),
            ));
        }

        $lostExp = $this->experienceService->lostExpAfterDeath($player);
        event(new PlayerDied($player));

        PlayerActiveEffect::query()
            ->where('player_id', $player->id)
            ->delete();

        $this->applyRevivalEffect($player, $lostExp, $result);
        $this->statService->invalidate($player);

        // Штраф упирается в начало уровня, поэтому в начале уровня он меньше
        // расчётных 10% (а на нулевом прогрессе — нулевой). Пишем в лог, что
        // списано на самом деле, иначе строка «-10%» противоречит счётчику.
        $result->log(sprintf(
            '<p>Вы <font color="red"><b>проиграли</b></font>! %s</p>',
            $this->expPenaltyText($player, $lostExp),
        ));

        $dungeonDeathMessage = $this->dungeonCoordinator->handlePlayerDeath($player);
        if ($dungeonDeathMessage !== null) {
            $result->log('<p><b>'.$dungeonDeathMessage.'</b></p>');
        }

        return true;
    }

    /**
     * Накладывает «Возрождение» на 30 минут. Сумма потерянного опыта хранится
     * в current_value активного эффекта — оттуда её читает и списывает
     * RestoreLostExpStrategy при использовании предмета из премиум-магазина.
     * Эффект накладывается и при нулевой потере (штраф срезался о начало
     * уровня): предмет для возврата опыта в этом случае просто откажет —
     * см. PlayerRevivalService::findActiveRedeemable().
     */
    private function applyRevivalEffect(Player $player, int $lostExp, AttackResultDTO $result): void
    {
        $effect = Effect::query()->where('slug', PlayerRevivalService::EFFECT_SLUG)->first();

        if ($effect === null) {
            return;
        }

        $this->battleEffectService->applyEffectToPlayer(
            $effect,
            $player,
            null,
            $result,
            PlayerRevivalService::DURATION_SECONDS,
            $lostExp,
        );
    }

    /**
     * Текст о штрафе с фактическими числами. Процент показываем, только если
     * он различим: у самого начала уровня штраф срезается почти в ноль, и
     * «(0%)» рядом с непустым числом читалось бы как противоречие.
     */
    private function expPenaltyText(Player $player, int $lostExp): string
    {
        if ($lostExp <= 0) {
            return 'Опыт не потерян — уровень только начат.';
        }

        $band = (int) $player->exp_diff;
        $percent = $band > 0 ? $lostExp * 100 / $band : 0.0;

        if ($percent < 0.1) {
            return sprintf('Опыт -%s.', number_format($lostExp, 0, '.', ' '));
        }

        return sprintf(
            'Опыт -%s (%s%%).',
            number_format($lostExp, 0, '.', ' '),
            rtrim(rtrim(number_format($percent, 1, '.', ''), '0'), '.'),
        );
    }
}
