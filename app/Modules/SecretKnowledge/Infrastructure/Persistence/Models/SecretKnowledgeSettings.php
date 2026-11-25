<?php

declare(strict_types=1);

namespace App\Modules\SecretKnowledge\Infrastructure\Persistence\Models;

use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SecretKnowledgeSettings extends Model
{
    protected $guarded = [];

    protected $casts = [
        'analysis_success_chance' => 'integer',
        'knowledge_gain_min' => 'integer',
        'knowledge_gain_max' => 'integer',
        'is_active' => 'boolean',
    ];

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class);
    }

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'bundle_share_item_id');
    }
}
