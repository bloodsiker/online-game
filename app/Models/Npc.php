<?php

namespace App\Models;

use App\Models\Quest\Quest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Npc extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'image', 'location_id'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class,'location_id');
    }

    public function startQuests(): HasMany
    {
        return $this->hasMany(Quest::class, 'start_npc_id');
    }

    public function completeQuests(): HasMany
    {
        return $this->hasMany(Quest::class, 'complete_npc_id');
    }

    public function structures(): HasMany
    {
        return $this->hasMany(Structure::class, 'npc_id')->with(['actions']);
    }
}
