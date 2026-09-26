<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DungeonRunParticipant extends Model
{
    protected $fillable = ['dungeon_run_id', 'user_id', 'dungeon_session_id', 'status', 'joined_at', 'finished_at'];

    protected $casts = ['joined_at' => 'datetime', 'finished_at' => 'datetime'];

    public function run(): BelongsTo
    {
        return $this->belongsTo(DungeonRun::class, 'dungeon_run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(DungeonSession::class, 'dungeon_session_id');
    }
}
