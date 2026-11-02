<?php

declare(strict_types=1);

namespace App\Modules\TreasureHunt\Infrastructure\Persistence\Models;

use App\Modules\Location\Infrastructure\Persistence\Models\Map;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class TreasureHunt extends Model
{
    protected $guarded = [];

    protected $casts = [
        'activated_at' => 'datetime', 'started_at' => 'datetime', 'completes_at' => 'datetime',
        'expires_at' => 'datetime', 'found_at' => 'datetime', 'chance_snapshot' => 'float',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(TreasureMapConfig::class, 'treasure_map_config_id');
    }

    public function targetMap(): BelongsTo
    {
        return $this->belongsTo(Map::class, 'target_map_id');
    }

    public static function isSearching(int $userId): bool
    {
        return Schema::hasTable('treasure_hunts') && self::query()->where('user_id', $userId)
            ->whereNull('found_at')->whereNotNull('map_item_id')->where('expires_at', '>', now())->exists();
    }

    public static function cancelSearchForUser(int $userId): void
    {
        if (Schema::hasTable('treasure_hunts')) {
            self::query()->where('user_id', $userId)->whereNull('found_at')
                ->update(['search_token' => null, 'started_at' => null, 'completes_at' => null, 'expires_at' => null, 'chance_snapshot' => null]);
        }
    }
}
