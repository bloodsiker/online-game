<?php

declare(strict_types=1);

namespace App\Modules\Race\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RaceAvatar extends Model
{
    protected $fillable = ['race_id', 'sex', 'name', 'image_path', 'sort_order'];

    protected $appends = ['image_url'];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}
