<?php

namespace App\Modules\Battle\Application\Services\Combat\Boss;

interface BossMechanicInterface
{
    /**
     * Перевірка чи можна активувати механіку
     */
    public function canTrigger(BossFightContext $context): bool;

    /**
     * Виконання механіки
     */
    public function execute(BossFightContext $context): void;

    /**
     * Опис механіки для логу
     */
    public function getDescription(): string;

    /**
     * Пріоритет виконання (більше = раніше)
     */
    public function getPriority(): int;
}
