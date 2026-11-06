<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\RecipeUnlockType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShareRecipe extends Model
{
    use HasFactory;

    protected $casts = [
        'unlock_type' => RecipeUnlockType::class,
        'is_random_result' => 'boolean',
    ];

    public function isLearnable(): bool
    {
        return $this->unlock_type === RecipeUnlockType::LEARNABLE;
    }

    public function scopeDisplayOrder(Builder $query): Builder
    {
        // Newly created recipes (0) follow the explicitly arranged ones.
        return $query->orderByRaw('share_recipes.sort_order = 0')->orderBy('share_recipes.sort_order')->orderBy('share_recipes.id');
    }

    public function itemInfo(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function kraftItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'kraft_item_id');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'share_recipe_has_items', 'share_recipe_id', 'share_item_id')
            ->withPivot(['id', 'count', 'sort_order'])
            ->orderByPivot('sort_order')->orderByPivot('id');
    }

    /** Пул случайного результата (используется, когда is_random_result=true, вместо kraftItem). */
    public function resultPool(): BelongsToMany
    {
        return $this->belongsToMany(ShareItem::class, 'share_recipe_results', 'share_recipe_id', 'share_item_id')
            ->withPivot('chance');
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'player_recipes')->withTimestamps();
    }
}
