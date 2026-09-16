<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryCategory extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(LibraryArticle::class, 'category_id');
    }

    public function publishedArticlesCountWithChildren(): int
    {
        return (int) ($this->articles_count ?? 0)
            + $this->children->sum(fn (self $child): int => $child->publishedArticlesCountWithChildren());
    }
}
