<?php

declare(strict_types=1);

namespace App\Modules\Notification\Infrastructure\Persistence\Models;

use App\Modules\Chat\Domain\Models\ChatMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlayerNotification extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['translation_params' => 'array', 'delivered_at' => 'datetime', 'read_at' => 'datetime'];
    }

    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class);
    }
}
