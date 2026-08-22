<?php

declare(strict_types=1);

namespace App\Modules\Item\Infrastructure\Persistence\Models;

use App\Modules\Item\Domain\Enums\ItemActionType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemActionLog extends Model
{
    protected $fillable = [
        'user_id', 'share_item_id', 'item_name', 'upgrade_lvl',
        'action', 'count', 'money', 'target_user_id',
    ];

    protected $casts = [
        'action' => ItemActionType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }
}
