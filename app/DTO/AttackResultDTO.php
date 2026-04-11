<?php

namespace App\DTO;

final class AttackResultDTO
{
    private array $logs = [];

    public function log(string $text): self
    {
        $this->logs[] = $text;
        return $this;
    }

    public function getLog(): string
    {
        return implode('', $this->logs);
    }

    public function merge(AttackResultDTO $other): self
    {
        foreach ($other->logs as $entry) {
            $this->logs[] = $entry;
        }

        return $this;
    }
}
