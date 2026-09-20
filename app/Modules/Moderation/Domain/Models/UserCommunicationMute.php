<?php

declare(strict_types=1);

namespace App\Modules\Moderation\Domain\Models;

use App\Modules\Moderation\Domain\Enums\CommunicationScope;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCommunicationMute extends Model
{
    protected $fillable = [
        'user_id',
        'imposed_by_user_id',
        'scope',
        'reason',
        'starts_at',
        'expires_at',
        'revoked_at',
        'revoked_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'scope' => CommunicationScope::class,
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imposed_by_user_id');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>', now());
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null
            && $this->starts_at->lte(now())
            && $this->expires_at->isFuture();
    }

    public function remainingLabel(): string
    {
        $remainingSeconds = max(0, now()->diffInSeconds($this->expires_at, false));

        if ($remainingSeconds < 60) {
            return 'менее минуты';
        }

        $remainingMinutes = (int) ceil($remainingSeconds / 60);
        $days = intdiv($remainingMinutes, 1440);
        $hours = intdiv($remainingMinutes % 1440, 60);
        $minutes = $remainingMinutes % 60;
        $parts = [];

        if ($days > 0) {
            $parts[] = $days.' д.';
        }
        if ($hours > 0) {
            $parts[] = $hours.' ч.';
        }
        if ($minutes > 0 && $days === 0) {
            $parts[] = $minutes.' мин.';
        }

        return implode(' ', array_slice($parts, 0, 2));
    }

    public function tooltip(): string
    {
        return 'Проклятие молчания. Осталось: '.$this->remainingLabel();
    }
}
