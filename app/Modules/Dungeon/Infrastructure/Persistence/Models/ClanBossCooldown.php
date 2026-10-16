<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Infrastructure\Persistence\Models;

use App\Modules\Clan\Domain\Models\Clan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanBossCooldown extends Model
{
    protected $fillable = ['dungeon_stage_id', 'clan_id', 'available_at'];

    protected $casts = [
        'available_at' => 'datetime',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(DungeonStage::class, 'dungeon_stage_id');
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }
}
