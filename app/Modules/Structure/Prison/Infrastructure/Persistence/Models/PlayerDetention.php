<?php

declare(strict_types=1);

namespace App\Modules\Structure\Prison\Infrastructure\Persistence\Models;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class PlayerDetention extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'prison_structure_id', 'prison_location_id', 'prison_name',
        'imposed_by_user_id', 'imposed_by_name', 'previous_location_id',
        'reason', 'starts_at', 'release_at', 'released_at', 'fine_money', 'fine_diamond',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'release_at' => 'datetime',
            'released_at' => 'datetime',
            'fine_money' => 'integer',
            'fine_diamond' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prison(): BelongsTo
    {
        return $this->belongsTo(Structure::class, 'prison_structure_id');
    }

    public function imposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imposed_by_user_id');
    }

    public function isActive(): bool
    {
        return $this->released_at === null && $this->release_at->isFuture();
    }

    public function actualReleaseAt(): Carbon
    {
        return $this->released_at ?? $this->release_at;
    }

    public function servedMinutes(): int
    {
        return (int) $this->starts_at->diffInMinutes($this->actualReleaseAt());
    }

    /** @return array{money:int, diamond:float} */
    public function currentBuyoutAmounts(?Carbon $at = null): array
    {
        $at ??= now();
        $totalSeconds = max(1, $this->release_at->getTimestamp() - $this->starts_at->getTimestamp());
        $remainingSeconds = max(0, $this->release_at->getTimestamp() - $at->getTimestamp());
        if ($remainingSeconds === 0) {
            return ['money' => 0, 'diamond' => 0.0];
        }

        $moneyFine = max(0, (int) $this->fine_money);
        $diamondFine = max(0.0, (float) $this->fine_diamond);

        return [
            'money' => $moneyFine > 0 ? min($moneyFine, max(1, (int) ceil($moneyFine * $remainingSeconds / $totalSeconds))) : 0,
            'diamond' => $diamondFine > 0
                ? min($diamondFine, max(0.01, ceil($diamondFine * $remainingSeconds / $totalSeconds * 100) / 100))
                : 0.0,
        ];
    }
}
