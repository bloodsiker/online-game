<?php

declare(strict_types=1);

namespace App\Modules\Gift\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Подарок, полученный игроком. Хранится навсегда; sender_id обнуляется, если даритель удалён.
 *
 * @property int $id
 * @property int $recipient_id
 * @property ?int $sender_id
 * @property int $share_item_id
 * @property ?string $message
 * @property bool $is_anonymous
 */
class PlayerGift extends Model
{
    protected $fillable = ['recipient_id', 'sender_id', 'share_item_id', 'message', 'is_anonymous'];

    protected function casts(): array
    {
        return ['is_anonymous' => 'boolean'];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }

    /** Даритель, которого можно показывать: анонимный подарок и удалённый игрок — без имени. */
    public function visibleSender(): ?User
    {
        return $this->is_anonymous ? null : $this->sender;
    }
}
