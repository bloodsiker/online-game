<?php

declare(strict_types=1);

namespace App\Modules\Festival\Infrastructure\Persistence\Models;

use App\Modules\Festival\Domain\Enums\FestivalSide;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FestivalParticipant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'points' => 'integer',
        'reward_claimed_at' => 'datetime',
    ];

    public function festival(): BelongsTo
    {
        return $this->belongsTo(Festival::class);
    }

    public function sideEnum(): FestivalSide
    {
        return FestivalSide::from($this->side);
    }
}
