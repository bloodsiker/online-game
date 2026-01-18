<?php

namespace App\Models\Share;

use App\Models\Structure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShareAction extends Model
{
    use HasFactory;

    public function structures(): BelongsToMany
    {
        return $this->belongsToMany(Structure::class, 'structure_actions', 'share_action_id', 'structure_id');
    }
}
