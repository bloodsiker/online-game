<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerRecipe extends Model
{
    protected $fillable = ['player_id', 'share_recipe_id'];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(ShareRecipe::class, 'share_recipe_id');
    }
}
