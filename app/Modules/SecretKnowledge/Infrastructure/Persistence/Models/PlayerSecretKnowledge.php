<?php

declare(strict_types=1);

namespace App\Modules\SecretKnowledge\Infrastructure\Persistence\Models;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlayerSecretKnowledge extends Model
{
    protected $guarded = [];

    protected $casts = ['points' => 'integer'];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
