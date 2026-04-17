<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use App\Enums\DungeonParticipantStatus;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $run_id
 * @property int $user_id
 * @property DungeonParticipantStatus $status
 * @property-read DungeonRun $run
 * @property-read User       $user
 */
class DungeonRunParticipant extends Model
{
    public $timestamps = false;

    protected $fillable = ['run_id', 'user_id', 'status'];

    protected $casts = [
        'status' => DungeonParticipantStatus::class,
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(DungeonRun::class, 'run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === DungeonParticipantStatus::ACTIVE;
    }
}
