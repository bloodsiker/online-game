<?php

declare(strict_types=1);

namespace App\Modules\Npc\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NpcDialogueNode extends Model
{
    protected $fillable = [
        'npc_id',
        'title',
        'description',
        'is_start',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_start' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function npc(): BelongsTo
    {
        return $this->belongsTo(Npc::class, 'npc_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(NpcDialogueOption::class, 'npc_dialogue_node_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function activeOptions(): HasMany
    {
        return $this->options()
            ->where('is_active', true)
            ->whereHas('toNode', fn ($query) => $query->where('is_active', true));
    }
}