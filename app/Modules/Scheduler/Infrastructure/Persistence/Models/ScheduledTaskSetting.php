<?php

declare(strict_types=1);

namespace App\Modules\Scheduler\Infrastructure\Persistence\Models;

use App\Modules\Scheduler\Domain\Enums\ScheduledTaskFrequency;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledTaskSetting extends Model
{
    protected $fillable = [
        'task_key',
        'enabled',
        'frequency',
        'last_status',
        'last_started_at',
        'last_finished_at',
        'last_duration_ms',
        'last_output',
        'last_error',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'frequency' => ScheduledTaskFrequency::class,
            'last_started_at' => 'datetime',
            'last_finished_at' => 'datetime',
            'last_duration_ms' => 'integer',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
