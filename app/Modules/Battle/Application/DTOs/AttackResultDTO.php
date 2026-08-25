<?php

namespace App\Modules\Battle\Application\DTOs;

use App\Modules\Effect\Application\DTOs\PlayerEffectNotificationDTO;

final class AttackResultDTO
{
    private array $logs = [];

    /**
     * Разовые уведомления (прогресс квестов и т.п.): показываются игроку
     * сразу после этого хода, но не входят в getLog() — не сохраняются в
     * battle_rounds/battle_round_hits и не всплывают повторно в истории боя.
     */
    private array $sideLogs = [];

    /** @var list<PlayerEffectNotificationDTO> */
    private array $playerEffects = [];

    public function log(string $text): self
    {
        $this->logs[] = $text;

        return $this;
    }

    public function getLog(): string
    {
        return implode('', $this->logs);
    }

    public function logSide(string $text): self
    {
        $this->sideLogs[] = $text;

        return $this;
    }

    public function getSideLog(): string
    {
        return implode('', $this->sideLogs);
    }

    public function notifyPlayerEffect(PlayerEffectNotificationDTO $effect): self
    {
        foreach ($this->playerEffects as $index => $current) {
            if ($current->id === $effect->id) {
                $this->playerEffects[$index] = $effect;

                return $this;
            }
        }

        $this->playerEffects[] = $effect;

        return $this;
    }

    /** @return list<PlayerEffectNotificationDTO> */
    public function getPlayerEffects(): array
    {
        return $this->playerEffects;
    }

    public function merge(AttackResultDTO $other): self
    {
        foreach ($other->logs as $entry) {
            $this->logs[] = $entry;
        }

        foreach ($other->sideLogs as $entry) {
            $this->sideLogs[] = $entry;
        }

        foreach ($other->playerEffects as $effect) {
            $this->notifyPlayerEffect($effect);
        }

        return $this;
    }
}
