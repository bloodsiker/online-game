<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Models;

use App\Models\Item\Item;
use App\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanWarehouse extends Model
{
    protected $fillable = ['clan_id', 'structure_id', 'depositor_user_id', 'item_id', 'count'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id')->with(['itemInfo']);
    }

    public function depositor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'depositor_user_id');
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}
