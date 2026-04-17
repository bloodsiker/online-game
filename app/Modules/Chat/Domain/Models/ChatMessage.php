<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Models;

use App\Modules\Chat\Domain\Enums\ChatChannel;
use App\Modules\Chat\Domain\Enums\ChatMessageType;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property ChatChannel $channel
 * @property int|null $target_user_id
 * @property int|null $clan_id
 * @property int|null $map_id
 * @property string $message
 * @property ChatMessageType $type
 * @property-read User|null $sender
 * @property-read User|null $target
 */
class ChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'channel',
        'target_user_id',
        'clan_id',
        'map_id',
        'message',
        'type',
    ];

    protected $casts = [
        'channel' => ChatChannel::class,
        'type' => ChatMessageType::class,
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
