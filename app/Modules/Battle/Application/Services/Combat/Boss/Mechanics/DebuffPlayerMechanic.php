<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Monster\Infrastructure\Persistence\Models\BossMechanic;

/**
 * Ослабление игрока - накладывает на игрока существующий боевой эффект
 * (яд/кровотечение/оглушение/паралич) через уже рабочий конвейер
 * BattleEffectService::applyCustomEffectToPlayer() - тот же, которым
 * пользуется пассивка "Оглушение" у рун и божественная немилость Кайрелла.
 * Намеренно не изобретаю отдельный ключ в boss_metadata со статами
 * (armor/dodge и т.п.) - для произвольного "-X к стату" нужна полноценная
 * запись в таблице effects со stat_modifiers, которой здесь взять негде.
 *
 * config:
 *   effect_type: 'poison'|'bleed'|'stun'|'paralysis' (default 'poison')
 *   value: урон за тик для poison/bleed, не используется для stun/paralysis
 *   duration: для poison/bleed - количество тиков, для stun/paralysis - секунды
 */
class DebuffPlayerMechanic extends BaseBossMechanic
{
    public function __construct(
        BossMechanic $mechanic,
        private readonly BattleEffectService $effectService,
    ) {
        parent::__construct($mechanic);
    }

    public function execute(BossFightContext $context): void
    {
        $effectType = $this->resolveEffectType();
        $value = (float) $this->getConfig('value', 10);
        $duration = (int) $this->getConfig('duration', $effectType->isControl() ? 2 : 3);

        $this->effectService->applyCustomEffectToPlayer(
            $effectType,
            $value,
            $duration,
            $context->getPlayer(),
            $context->getBattle(),
            $context->getResult(),
        );

        $this->markAsTriggered($context);
    }

    public function getDescription(): string
    {
        return 'Босс ослабляет игрока: '.$this->resolveEffectType()->label();
    }

    private function resolveEffectType(): ActiveEffectType
    {
        return ActiveEffectType::tryFrom((string) $this->getConfig('effect_type', 'poison')) ?? ActiveEffectType::POISON;
    }
}
