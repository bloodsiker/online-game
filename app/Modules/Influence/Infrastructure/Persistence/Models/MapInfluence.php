<?php

declare(strict_types=1);

namespace App\Modules\Influence\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapInfluence extends Model
{
    protected $fillable = ['map_id', 'user_id', 'influence'];

    protected $casts = ['influence' => 'integer'];

    public function map(): BelongsTo
    {
        return $this->belongsTo(Map::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
