<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models;

use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReputationExchange extends Model
{
    protected $fillable = [
        'structure_id', 'share_item_id', 'reputation_id',
        'points', 'min_reputation', 'max_reputation', 'sort_order',
    ];

    protected $attributes = [
        'points' => 5,
        'min_reputation' => 0,
        'sort_order' => 0,
    ];

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function isInBracket(int $currentPoints): bool
    {
        return $currentPoints >= $this->min_reputation && $currentPoints < $this->max_reputation;
    }

    /**
     * На первом ранге можно сдавать любой реликт: даже более редкий всё равно
     * даёт стандартные 5 очков. После 500 действуют пороги самого реликта.
     */
    public function isAcceptedAt(int $currentPoints): bool
    {
        return $currentPoints < 500 || $this->isInBracket($currentPoints);
    }
}
