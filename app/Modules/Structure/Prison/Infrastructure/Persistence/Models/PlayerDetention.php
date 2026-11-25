<?php

declare(strict_types=1);

namespace App\Modules\Structure\Prison\Infrastructure\Persistence\Models;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

    /** Не освобождён и срок не истёк (бессрочное заключение — release_at = NULL — активно всегда). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('released_at')
            ->where(fn (Builder $term) => $term->whereNull('release_at')->orWhere('release_at', '>', now()));
    }

    public function isIndefinite(): bool
    {
        return $this->release_at === null;
    }

    public function isActive(): bool
    {
        return $this->released_at === null && ($this->release_at === null || $this->release_at->isFuture());
    }

    /** Дата освобождения для игрока и админки: «бессрочно» или d.m.Y H:i. */
    public function releaseLabel(): string
    {
        return $this->release_at?->format('d.m.Y H:i') ?? 'бессрочно';
    }

    /** Фактическое освобождение; null — бессрочный срок ещё идёт. */
    public function actualReleaseAt(): ?Carbon
    {
        return $this->released_at ?? $this->release_at;
    }

    public function servedMinutes(): int
    {
        return (int) $this->starts_at->diffInMinutes($this->actualReleaseAt() ?? now());
    }

    /** @return array{money:int, diamond:float} */
    public function currentBuyoutAmounts(?Carbon $at = null): array
    {
        $at ??= now();
        // Бессрочное заключение выкупить нельзя.
        if ($this->release_at === null) {
            return ['money' => 0, 'diamond' => 0.0];
        }
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
