<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemEffect\Strategies;

use App\Modules\Item\Application\ItemEffect\ValueObjects\ItemEffectValue;
use App\Modules\Player\Domain\Events\PlayerLeveledUp;
use App\Modules\Player\Domain\Services\PlayerRevivalService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

/**
 * Возвращает опыт, потерянный при последней смерти, пока активно
 * «Возрождение» (см. PlayerDeathFinalizer::applyRevivalEffect()). Сумма
 * берётся не из конфигурации предмета, а из current_value активного
 * эффекта — предмет лишь запускает возврат.
 */
class RestoreLostExpStrategy implements ItemEffectStrategyInterface
{
    public function __construct(private readonly PlayerRevivalService $revivalService) {}

    public function apply(Player $player, ItemEffectValue $effect, ?int $hpMax = null, ?int $mpMax = null): void
    {
        $revival = $this->revivalService->findActiveRedeemable($player);

        // Контроллер уже отклоняет использование без активного окна — см.
        // ItemController::useItem(). Это защита на случай гонки запросов.
        if ($revival === null) {
            return;
        }

        $player->exp += (int) $revival->current_value;

        // Восстановленный опыт мог оказаться уже выше exp_up (если игрок
        // получил уровень между смертью и использованием предмета) —
        // проводим через ту же проверку, что и обычный опыт с боя.
        while ($player->exp >= $player->exp_up) {
            $player->lvl++;
            $player->save();

            event(new PlayerLeveledUp($player));
        }

        $player->save();

        // Возврат одноразовый: после него в «Возрождении» нет смысла держать
        // игрока (обнулённый current_value всё равно навсегда заблокировал бы
        // повторный возврат) — снимаем эффект сразу, а не ждём истечения
        // 30 минут. Иконка на странице героя пропадает по removed_effects
        // в ответе ItemController::useItem(), не дожидаясь heartbeat.
        $revival->delete();
    }
}
