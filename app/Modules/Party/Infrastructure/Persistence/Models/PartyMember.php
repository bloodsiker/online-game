<?php

declare(strict_types=1);

namespace App\Modules\Party\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $party_id
 * @property int $user_id
 * @property bool $is_ready
 * @property-read Party $party
 * @property-read User  $user
 */
class PartyMember extends Model
{
    protected $fillable = ['party_id', 'user_id', 'is_ready'];

    protected $casts = [
        'is_ready' => 'boolean',
    ];

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
