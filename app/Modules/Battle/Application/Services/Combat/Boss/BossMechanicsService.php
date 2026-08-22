<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Domain\Enums\BossMechanicType;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Monster\Infrastructure\Persistence\Models\BossDefeatLog;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\Log;

class BossMechanicsService
{
    /**
     * Обробка всіх механік боса
     */
    public function processMechanics(
        MonsterOnLocation $locationMonster,
        Battle $battle,
        Player $player,
        AttackResultDTO $result
    ): void {
        $monster = $locationMonster->monster;

        if (! $monster->isBoss()) {
            return;
        }

        $mechanics = $monster->mechanics()
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($mechanics as $mechanicModel) {
            try {
                // Механіки без cooldown_turns — одноразові (як і раніше): спрацювала раз за бій — і все.
                // Механіки з cooldown_turns у config можуть спрацьовувати повторно — за це відповідає
                // canTrigger() нижче (перевіряє mechanic_data[last_turn] + cooldown_turns).
                $isRepeatable = isset($mechanicModel->config['cooldown_turns']);
                if (! $isRepeatable && $this->wasMechanicTriggered($battle, $mechanicModel->id)) {
                    continue;
                }

                $mechanic = $mechanicModel->getInstance();

                $context = new BossFightContext($locationMonster, $battle, $player, $result);

                if ($mechanic->canTrigger($context)) {
                    $mechanic->execute($context);

                    // Зберігаємо що механіка спрацювала
                    $this->markMechanicAsTriggered($battle, $mechanicModel);

                    $result->log(sprintf(
                        '<p><b class="color-boss">%s</b></p>',
                        $mechanic->getDescription()
                    ));
                }
            } catch (\Exception $e) {
                Log::error('Failed to execute boss mechanic', [
                    'mechanic_id' => $mechanicModel->id,
                    'battle_id' => $battle->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Обробка смерті боса
     */
    public function handleBossDeath(
        MonsterOnLocation $locationMonster,
        Battle $battle,
        Player $player,
        AttackResultDTO $result
    ): void {
        $monster = $locationMonster->monster;

        // Спеціальні механіки при смерті (наприклад, вибух, прокляття)
        $deathMechanics = $monster->mechanics()
            ->where('mechanic_type', BossMechanicType::DEATH_EXPLOSION)
            ->where('is_active', true)
            ->get();

        foreach ($deathMechanics as $mechanicModel) {
            try {
                $mechanic = $mechanicModel->getInstance();
                $context = new BossFightContext($locationMonster, $battle, $player, $result);

                $mechanic->execute($context);

                $result->log(sprintf(
                    '<p><b class="color-death">%s умирает: %s</b></p>',
                    $monster->name,
                    $mechanic->getDescription()
                ));
            } catch (\Exception $e) {
                Log::error('Failed to execute death mechanic', [
                    'mechanic_id' => $mechanicModel->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Логуємо перемогу над босом
        BossDefeatLog::create([
            'battle_id' => $battle->id,
            'monster_id' => $monster->id,
            'player_id' => $player->id,
            'turns_taken' => $battle->rounds,
            'mechanics_triggered' => $battle->boss_metadata['mechanics_triggered'] ?? [],
            'final_phase' => $locationMonster->current_phase ?? 1,
        ]);

        $result->log(sprintf(
            '<p><b class="color-legendary">🏆 Вы победили босса %s! 🏆</b></p>',
            $monster->name
        ));
    }

    private function wasMechanicTriggered(Battle $battle, int $mechanicId): bool
    {
        $triggeredMechanics = $battle->boss_metadata['mechanics_triggered'] ?? [];

        return in_array($mechanicId, $triggeredMechanics);
    }

    private function markMechanicAsTriggered(Battle $battle, $mechanic): void
    {
        $metadata = $battle->boss_metadata ?? [];
        $metadata['mechanics_triggered'][] = $mechanic->id;

        // Якщо механіка одноразова - деактивуємо
        if ($mechanic->config['one_time'] ?? false) {
            $mechanic->is_active = false;
            $mechanic->save();
        }

        $battle->boss_metadata = $metadata;
        $battle->save();
    }
}
