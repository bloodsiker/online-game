<?php

namespace App\Models\Clan;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Clan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'news_1', 'news_2', 'news_3', 'icon', 'owner_id', 'lvl', 'warehouse_capacity'];

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

    public function roles(): HasMany
    {
        return $this->hasMany(ClanRole::class)->orderBy('id');
    }
}
