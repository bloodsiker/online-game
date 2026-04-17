<?php

declare(strict_types=1);

namespace App\Models\Dungeon;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $run_id
 * @property int|null $user_id
 * @property string $event
 * @property array|null $payload
 * @property-read DungeonRun $run
 * @property-read User|null  $user
 */
class DungeonRunLog extends Model
{
    protected $table = 'dungeon_run_log';

    public $timestamps = false;

    public $updatable = false;

    protected $fillable = ['run_id', 'user_id', 'event', 'payload', 'created_at'];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(DungeonRun::class, 'run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
