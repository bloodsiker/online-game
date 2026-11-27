<?php

declare(strict_types=1);

namespace App\Modules\Location\Domain\Traps;

use App\Modules\Battle\Application\Services\Combat\TimedEffectDeathService;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationTrap;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Опасная переправа (разрушенный мост, гнилой настил…): игрок делает `steps`
 * шагов, на каждом с шансом `chance` % проваливается и теряет `damage_percent` %
 * от максимального HP, после чего идёт дальше. Если HP упало до нуля — смерть.
 *
 * После каждого провала (если игрок жив) может накладываться яд-эффект: `effect_slug`
 * на `effect_seconds` секунд, каждый тик отнимает `effect_percent` % от максимального HP.
 *
 * config: steps (3), chance (30), damage_percent (50), effect_slug, effect_seconds (60), effect_percent (1).
 */
final readonly class CrossingTrap implements TrapType
{
    public function __construct(
        private PlayerStatService $statService,
        private TimedEffectDeathService $deathService,
        private TrapEffectApplier $effectApplier,
    ) {}

    public function label(): string
    {
        return 'Переправа (мост, река, настил)';
    }

    public function configFields(): array
    {
        return [
            ['key' => 'steps', 'label' => 'Шагов (бросков на провал)', 'kind' => 'number', 'default' => 3, 'hint' => 'Каждый шаг — отдельный бросок шанса, провалов может быть до этого числа.'],
            ['key' => 'chance', 'label' => 'Шанс провала на шаге, %', 'kind' => 'number', 'default' => 30],
            ['key' => 'damage_percent', 'label' => 'Урон за провал, % от макс. HP', 'kind' => 'number', 'default' => 50],
            ['key' => 'effect_slug', 'label' => 'Эффект при провале (необязательно)', 'kind' => 'effect', 'default' => '', 'hint' => 'Накладывается после урона, если игрок жив.'],
            ['key' => 'effect_seconds', 'label' => 'Длительность эффекта, сек', 'kind' => 'number', 'default' => 60],
            ['key' => 'effect_percent', 'label' => 'Урон эффекта за тик, % от макс. HP', 'kind' => 'number', 'default' => 1, 'step' => '0.1', 'hint' => 'Только для периодических эффектов (яд, ожог, кровотечение).'],
        ];
    }

    public function resolve(User $user, LocationTrap $trap): TrapOutcome
    {
        $steps = max(1, (int) $trap->setting('steps', 3));
        $chance = max(0, min(100, (int) $trap->setting('chance', 30)));
        $percent = max(0, min(100, (int) $trap->setting('damage_percent', 50)));

        return DB::transaction(function () use ($user, $trap, $steps, $chance, $percent): TrapOutcome {
            $player = Player::query()->whereKey($user->player_id)->lockForUpdate()->firstOrFail();
            $player->setRelation('user', $user);

            if ((int) $player->hp_now <= 0) {
                return TrapOutcome::nothing();
            }

            $hpMax = $this->statService->resolve($player)->getHpMax();
            $damage = max(1, (int) round($hpMax * $percent / 100));
            $messages = [];
            $died = false;

            for ($step = 1; $step <= $steps; $step++) {
                if (random_int(1, 100) > $chance) {
                    continue;
                }

                $player->hp_now = max(0, (int) $player->hp_now - $damage);
                $messages[] = __('Вы провалились! Получено урона: :damage.', ['damage' => $damage]);

                if ((int) $player->hp_now <= 0) {
                    $died = true;
                    break;
                }

                if ($this->applyEffect($player, $trap, $hpMax)) {
                    $messages[] = __('Вас отравила вода.');
                }
            }

            if ($died) {
                $died = $this->deathService->handle($player, (string) $trap->name);
                $messages[] = __('Вы погибли при переправе.');
            }

            if ($messages !== []) {
                $player->save();
                $user->setRelation('player', $player);
            }

            return new TrapOutcome($messages, $died);
        });
    }

    private function applyEffect(Player $player, LocationTrap $trap, int $hpMax): bool
    {
        $slug = (string) $trap->setting('effect_slug', '');
        if ($slug === '') {
            return false;
        }

        $seconds = max(1, (int) $trap->setting('effect_seconds', 60));
        $perTick = max(1, (int) round($hpMax * max(0.0, (float) $trap->setting('effect_percent', 1)) / 100));

        return $this->effectApplier->apply($player, $slug, $seconds, $perTick);
    }
}
