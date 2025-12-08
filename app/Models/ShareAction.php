<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareAction extends Model
{
    use HasFactory;

    public function structures()
    {
        return $this->belongsToMany(Structure::class, 'structure_actions', 'share_action_id', 'structure_id');
    }
}
