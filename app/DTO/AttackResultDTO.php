<?php

namespace App\DTO;

class AttackResultDTO
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
