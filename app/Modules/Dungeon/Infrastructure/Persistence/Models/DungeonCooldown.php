<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DungeonCooldown extends Model
{
    protected $fillable = ['dungeon_id', 'user_id', 'available_at'];

    protected $casts = [
        'available_at' => 'datetime',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return now()->lt($this->available_at);
    }

    public function isGlobal(): bool
    {
        return $this->user_id === 0;
    }
}
