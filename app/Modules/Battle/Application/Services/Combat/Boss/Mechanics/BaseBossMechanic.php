<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss\Mechanics;

use App\Modules\Monster\Infrastructure\Persistence\Models\BossMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\BossFightContext;
use App\Modules\Battle\Application\Services\Combat\Boss\BossMechanicInterface;

abstract class BaseBossMechanic implements BossMechanicInterface
{
    protected BossMechanic $mechanic;

    protected array $config;

    public function __construct(BossMechanic $mechanic)
    {
        $this->mechanic = $mechanic;
        $this->config = $mechanic->config ?? [];
    }

    public function canTrigger(BossFightContext $context): bool
    {
        // Перевірка HP threshold
        if ($this->mechanic->trigger_hp_percent !== null) {
            if ($context->getBossHpPercent() > $this->mechanic->trigger_hp_percent) {
                return false;
            }
        }

        // Перевірка конкретного ходу
        if ($this->mechanic->trigger_turn !== null) {
            if ($context->getCurrentTurn() < $this->mechanic->trigger_turn) {
                return false;
            }
        }

        // Перевірка кулдауну (якщо механіка повторювана)
        if ($cooldown = $this->getConfig('cooldown_turns')) {
            $lastTrigger = $context->getMechanicData("mechanic_{$this->mechanic->id}_last_turn");

            if ($lastTrigger !== null) {
                if (($context->getCurrentTurn() - $lastTrigger) < $cooldown) {
                    return false;
                }
            }
        }

        return true;
    }

    public function getPriority(): int
    {
        return $this->mechanic->priority;
    }

    protected function getConfig(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    protected function markAsTriggered(BossFightContext $context): void
    {
        $context->setMechanicData(
            "mechanic_{$this->mechanic->id}_last_turn",
            $context->getCurrentTurn()
        );
    }
}
