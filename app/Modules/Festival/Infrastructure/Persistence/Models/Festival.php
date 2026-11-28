<?php

declare(strict_types=1);

namespace App\Modules\Festival\Infrastructure\Persistence\Models;

use App\Modules\Festival\Domain\Enums\FestivalSide;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Festival extends Model
{
    public const STATUS_COLLECTION = 'collection';

    public const STATUS_REWARDS = 'rewards';

    public const STATUS_FINISHED = 'finished';

    public const OUTCOME_WINNER = 'winner';

    public const OUTCOME_DRAW = 'draw';

    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'collection_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'collection_announced_at' => 'datetime',
        'rewards_announced_at' => 'datetime',
        'finished_announced_at' => 'datetime',
        'light_points' => 'integer',
        'dark_points' => 'integer',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(FestivalParticipant::class);
    }

    public function winnerSide(): ?FestivalSide
    {
        return $this->winner_side === null ? null : FestivalSide::from($this->winner_side);
    }
}
