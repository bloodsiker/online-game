<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DungeonSession extends Model
{
    protected $fillable = [
        'dungeon_id', 'user_id', 'primary_session_id', 'current_wave', 'entered_at', 'expires_at', 'completed_at',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gt($this->expires_at);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function monsterSessionId(): int
    {
        return $this->primary_session_id ?? $this->id;
    }
}
