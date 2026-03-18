<?php

namespace App\Models\Clan;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $clan_id
 * @property int $user_id
 * @property int $role_id
 * @property int $points
 *
 * @property-read User $user
 * @property-read Clan $clan
 * @property-read ClanRole $role
 */
class ClanMember extends Model
{
    use HasFactory;

    protected $fillable = ['role_id', 'clan_id', 'user_id', 'points'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(ClanRole::class);
    }
}
