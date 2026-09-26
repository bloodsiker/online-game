<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapInfluenceTransaction extends Model
{
    protected $fillable = [
        'user_id', 'map_id', 'amount', 'source_type', 'source_id',
        'idempotency_key', 'description', 'metadata',
    ];

    protected $casts = ['amount' => 'integer', 'metadata' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
