<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsPost extends Model
{
    protected $fillable = [
        'title',
        'description',
        'allow_comments',
        'views_count',
        'is_active',
        'created_at',
    ];

    protected $casts = [
        'allow_comments' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class);
    }

    public function visibleComments(): HasMany
    {
        return $this->comments()->where('is_visible', true);
    }
}
