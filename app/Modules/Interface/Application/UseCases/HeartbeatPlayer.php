<?php

declare(strict_types=1);

namespace App\Modules\Interface\Application\UseCases;

use App\Modules\Battle\Application\Services\Combat\TimedEffectDeathService;
use App\Modules\Interface\Application\DTOs\PlayerHeartbeatDTO;
use App\Modules\Interface\Application\Mappers\HeroPageViewMapper;
use App\Modules\Interface\Domain\Contracts\InterfaceReadRepository;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Domain\Services\PlayerTimedEffectService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class HeartbeatPlayer
{
    public function __construct(
        private PlayerStatService $statService,
        private PlayerTimedEffectService $timedEffectService,
        private TimedEffectDeathService $timedEffectDeathService,
        private InterfaceReadRepository $readRepository,
        private HeroPageViewMapper $heroMapper,
    ) {}

    public function execute(User $user, bool $touchOnline = true): PlayerHeartbeatDTO
    {
        return DB::transaction(function () use ($user, $touchOnline): PlayerHeartbeatDTO {
            $now = now();
            $player = Player::query()
                ->whereKey($user->player->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($touchOnline) {
                $user->forceFill(['last_online_at' => $now])->saveQuietly();
            }

            $sheet = $this->statService->resolve($player);
            if ((int) $player->hp_now > 0) {
                $player->regenerate($sheet->getHpMax(), $sheet->getMpMax());
            }

            $hpBeforeEffects = (int) $player->hp_now;
            $tickResult = $this->timedEffectService->process($player, $now);
            $dead = $hpBeforeEffects > 0 && (int) $player->hp_now <= 0;
            $deathMessage = null;

            if ($dead) {
                $effectNames = array_values(array_unique(array_column($tickResult->effects, 'label')));
                $effectName = implode(', ', $effectNames) ?: 'Периодический эффект';
                $dead = $this->timedEffectDeathService->handle($player, $effectName);

                if ($dead) {
                    $deathMessage = 'Вы погибли от периодического эффекта: '.$effectName.'. Опыт -10%.';
                }
            }

            if ($tickResult->effectsChanged) {
                $this->statService->invalidate($player);
            }

            $player->hp_now = min((int) $player->hp_now, $sheet->getHpMax());
            $player->mp_now = min((int) $player->mp_now, $sheet->getMpMax());
            if ($player->isDirty(['hp_now', 'mp_now'])) {
                $player->save();
            }

            return new PlayerHeartbeatDTO(
                serverTime: $now->getTimestamp(),
                lastOnlineAt: $now->toIso8601String(),
                effectDamage: $tickResult->totalDamage,
                hp: ['current' => (int) $player->hp_now, 'max' => $sheet->getHpMax()],
                mp: ['current' => (int) $player->mp_now, 'max' => $sheet->getMpMax()],
                effects: $this->heroMapper->mapEffects(
                    $this->readRepository->getPlayerActiveEffects((int) $player->id),
                    $now,
                ),
                dead: $dead,
                deathMessage: $deathMessage,
            );
        }, attempts: 3);
    }
}
