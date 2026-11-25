<?php

declare(strict_types=1);

namespace App\Modules\SecretKnowledge\Infrastructure\Persistence\Models;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BossKnowledgeManual extends Model
{
    protected $guarded = [];

    protected $casts = [
        'required_knowledge' => 'integer',
        'is_active' => 'boolean',
    ];

    public function manual(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'manual_share_item_id');
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    public function bossPart(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'boss_part_share_item_id');
    }
}
