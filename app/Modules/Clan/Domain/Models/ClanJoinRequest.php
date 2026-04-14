<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Models;

use App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ClanJoinRequestStatus $status
 * @property string $message
 *
 * @property-read User $user
 * @property-read Clan $clan
 */
class ClanJoinRequest extends Model
{
    use HasFactory;

    protected $fillable = ['clan_id', 'user_id', 'status', 'message'];

    protected $casts = [
        'status' => ClanJoinRequestStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }
}