<?php

namespace app\Models\Clan;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clan extends Model
{
    use HasFactory;

    protected $attributes = [
        'lvl' => 1,
    ];

    protected $with = ['owner'];

    public function members(): HasMany
    {
        return $this->hasMany(ClanMember::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
