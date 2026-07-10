<?php

declare(strict_types=1);

namespace App\Modules\Post\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostLetter extends Model
{
    protected $table = 'post_letters';

    protected $fillable = [
        'sender_user_id',
        'recipient_user_id',
        'subject',
        'text',
        'money',
        'money_claimed_at',
        'share_item_id',
        'item_amount',
        'item_claimed_at',
        'read_at',
        'sender_deleted_at',
        'recipient_deleted_at',
    ];

    protected $casts = [
        'money_claimed_at' => 'datetime',
        'item_claimed_at' => 'datetime',
        'read_at' => 'datetime',
        'sender_deleted_at' => 'datetime',
        'recipient_deleted_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function hasUnclaimedAttachments(): bool
    {
        return ($this->money > 0 && $this->money_claimed_at === null)
            || ($this->share_item_id !== null && $this->item_claimed_at === null);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isSystem(): bool
    {
        return $this->sender_user_id === null;
    }

    /**
     * Сколько письму осталось храниться у получателя: «2д 5ч», «45м»…
     * Непрочитанное живёт 30 дней с получения, прочитанное — 3 дня с прочтения.
     */
    public function storageLeft(): string
    {
        $expiresAt = $this->read_at
            ? $this->read_at->copy()->addDays(3)
            : $this->created_at->copy()->addDays(30);

        $minutes = (int) now()->diffInMinutes($expiresAt, false);

        if ($minutes <= 0) {
            return '—';
        }

        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);

        return match (true) {
            $days > 0 => $days.'д '.$hours.'ч',
            $hours > 0 => $hours.'ч '.($minutes % 60).'м',
            default => $minutes.'м',
        };
    }
}
