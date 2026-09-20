<?php

declare(strict_types=1);

namespace App\Modules\Scheduler\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledTaskRun extends Model
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const TRIGGER_SCHEDULER = 'scheduler';

    public const TRIGGER_MANUAL = 'manual';

    protected $fillable = [
        'task_key',
        'status',
        'trigger',
        'initiated_by',
        'started_at',
        'finished_at',
        'duration_ms',
        'output',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'duration_ms' => 'integer',
        ];
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
