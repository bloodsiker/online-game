<?php

namespace App\Models\Clan;

use App\Enums\ClanWarehouseAction;
use App\Models\Item\Item;
use App\Models\Structure;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanWarehouseLog extends Model
{
    protected $fillable = ['clan_id', 'user_id', 'structure_id', 'item_id', 'action', 'count'];

    protected $casts = [
        'action' => ClanWarehouseAction::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
