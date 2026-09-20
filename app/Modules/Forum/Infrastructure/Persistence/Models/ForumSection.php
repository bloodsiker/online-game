<?php

declare(strict_types=1);

namespace App\Modules\Forum\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ForumSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'sort_order', 'is_active',
        'allow_topics', 'allow_comments',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_topics' => 'boolean',
        'allow_comments' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function allChildren(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class, 'section_id');
    }

    public function latestTopic(): HasOne
    {
        return $this->hasOne(ForumTopic::class, 'section_id')->latestOfMany('last_post_at');
    }
}
