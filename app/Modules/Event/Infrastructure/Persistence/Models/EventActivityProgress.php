<?php

declare(strict_types=1);

namespace App\Modules\Event\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventActivityProgress extends Model
{
    use HasFactory;

    protected $table = 'event_activity_progresses';

    protected $fillable = [
        'user_id',
        'event_activity_id',
        'period_key',
        'progress',
        'completed_at',
        'reward_claimed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'reward_claimed_at' => 'datetime',
    ];

    protected $attributes = [
        'progress' => 0,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(EventActivity::class, 'event_activity_id');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }
}
