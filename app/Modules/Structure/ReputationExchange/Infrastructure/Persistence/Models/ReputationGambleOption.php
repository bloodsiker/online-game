<?php

declare(strict_types=1);

namespace App\Modules\Structure\ReputationExchange\Infrastructure\Persistence\Models;

use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReputationGambleOption extends Model
{
    protected $fillable = [
        'reputation_id',
        'share_item_id',
        'resource_cost',
        'success_chance',
        'reward_points',
        'sort_order',
    ];

    protected $casts = [
        'resource_cost' => 'integer',
        'success_chance' => 'integer',
        'reward_points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function reputation(): BelongsTo
    {
        return $this->belongsTo(Reputation::class);
    }

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }
}
