<?php

declare(strict_types=1);

namespace App\Modules\Forum\Infrastructure\Persistence\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id', 'user_id', 'player_id', 'author_name', 'title',
        'is_pinned', 'is_locked', 'views_count', 'posts_count', 'rating',
        'last_post_at', 'last_post_user_id',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'last_post_at' => 'datetime',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ForumSection::class, 'section_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lastPostAuthor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_post_user_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class, 'topic_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ForumTopicVote::class, 'topic_id');
    }
}
