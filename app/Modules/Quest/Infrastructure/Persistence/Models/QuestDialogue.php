<?php

declare(strict_types=1);

namespace App\Modules\Quest\Infrastructure\Persistence\Models;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestDialogue extends Model
{
    use HasLegacyTranslations;

    public array $translatable = ['description', 'reply_text'];

    protected $fillable = [
        'quest_id',
        'npc_id',
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

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class, 'npc_id');
    }
}
