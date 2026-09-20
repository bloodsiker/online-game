<?php

declare(strict_types=1);

namespace App\Modules\Quest\Domain\DTOs;

use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;

final readonly class QuestItemGateState
{
    /**
     * @param  array<int, int>  $backpackCounts
     * @param  list<string>  $missingItemNames
     */
    public function __construct(
        private array $backpackCounts,
        private array $missingItemNames,
    ) {}

    public function missingDescription(): ?string
    {
        return $this->missingItemNames === [] ? null : implode(', ', $this->missingItemNames);
    }

    public function initialAmountFor(QuestObjective $objective): int
    {
        if ($objective->type !== 'collect' || $objective->target_type !== 'item' || ! $objective->share_item_id) {
            return 0;
        }

        return min(
            (int) $objective->required_amount,
            $this->backpackCounts[(int) $objective->share_item_id] ?? 0,
        );
    }
}
