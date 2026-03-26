<?php

declare(strict_types=1);

namespace App\Models\Clan;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int    $clan_id
 * @property int    $structure_id
 * @property int    $user_id
 * @property string $action
 * @property int    $amount
 * @property int    $balance_after
 */
class ClanTreasuryLog extends Model
{
    protected $fillable = ['clan_id', 'structure_id', 'user_id', 'action', 'amount', 'balance_after'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }
}