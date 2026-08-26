<?php

declare(strict_types=1);

namespace App\Modules\Npc\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NpcDialogueOption extends Model
{
    protected $fillable = [
        'npc_dialogue_node_id',
        'to_node_id',
        'button_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(NpcDialogueNode::class, 'npc_dialogue_node_id');
    }

    public function toNode(): BelongsTo
    {
        return $this->belongsTo(NpcDialogueNode::class, 'to_node_id');
    }
}
