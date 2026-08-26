<?php

declare(strict_types=1);

namespace App\Modules\Party\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $party_id
 * @property int $inviter_user_id
 * @property int $invited_user_id
 * @property string $uuid
 * @property int|null $chat_message_id
 * @property string $status
 * @property-read Party $party
 * @property-read User  $inviter
 * @property-read User  $invited
 */
class PartyInvite extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = ['party_id', 'inviter_user_id', 'invited_user_id', 'uuid', 'chat_message_id', 'status'];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    public function invited(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
