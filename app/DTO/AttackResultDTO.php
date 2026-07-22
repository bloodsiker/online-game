<?php

namespace App\DTO;

final class AttackResultDTO
{
    private array $logs = [];

    /**
     * Разовые уведомления (прогресс квестов и т.п.): показываются игроку
     * сразу после этого хода, но не входят в getLog() — не сохраняются в
     * battle_rounds/battle_round_hits и не всплывают повторно в истории боя.
     */
    private array $sideLogs = [];

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

    public function merge(AttackResultDTO $other): self
    {
        foreach ($other->logs as $entry) {
            $this->logs[] = $entry;
        }

        foreach ($other->sideLogs as $entry) {
            $this->sideLogs[] = $entry;
        }

        return $this;
    }
}
