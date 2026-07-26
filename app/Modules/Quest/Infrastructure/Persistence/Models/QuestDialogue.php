<?php

declare(strict_types=1);

namespace App\Modules\Quest\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestDialogue extends Model
{
    protected $fillable = [
        'quest_id',
        'order',
        'description',
        'reply_text',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }
}
