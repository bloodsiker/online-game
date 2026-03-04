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
}
