<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int              $id
 * @property int              $dungeon_id
 * @property int              $user_id
 * @property int              $fail_count
 * @property \Carbon\Carbon|null $last_fail_at
 * @property \Carbon\Carbon|null $last_pity_at
 * @property-read Dungeon $dungeon
 * @property-read User    $user
 */
class DungeonPity extends Model
{
    protected $fillable = ['dungeon_id', 'user_id', 'fail_count', 'last_fail_at', 'last_pity_at'];

    protected $casts = [
        'last_fail_at' => 'datetime',
        'last_pity_at' => 'datetime',
    ];

    public function dungeon(): BelongsTo
    {
        return $this->belongsTo(Dungeon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPityReached(int $threshold): bool
    {
        return $this->fail_count >= $threshold;
    }
}