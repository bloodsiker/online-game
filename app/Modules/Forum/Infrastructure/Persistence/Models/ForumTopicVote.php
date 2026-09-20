<?php

declare(strict_types=1);

namespace App\Modules\Forum\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumTopicVote extends Model
{
    use HasFactory;

    protected $fillable = ['topic_id', 'user_id', 'value'];

    protected $casts = [
        'value' => 'integer',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }
}
