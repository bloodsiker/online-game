<?php

declare(strict_types=1);

namespace App\Modules\PlayerTrade\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerTradeSession extends Model
{
    public const PENDING = 'pending';

    public const ACTIVE = 'active';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'uuid', 'initiator_user_id', 'target_user_id', 'status', 'initiator_offer', 'target_offer',
        'initiator_confirmed', 'target_confirmed', 'chat_message_id', 'expires_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'initiator_offer' => 'array',
            'target_offer' => 'array',
            'initiator_confirmed' => 'boolean',
            'target_confirmed' => 'boolean',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_user_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
