<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Clan\ClanMember;
use App\Models\Player\Player;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'location_id',
        'prev_location_id',
        'last_online_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_online_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $with = ['player'];

    protected $attributes = [
        'is_admin' => false,
        'warehouse_count' => 50,
    ];

    public function backpack(): BelongsToMany
    {
        return $this->belongsToMany(Backpack::class, 'backpacks', 'user_id', 'item_id')->withPivot('equipped');
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class,'location_id');
    }

    public function prevLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class,'prev_location_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class,'player_id')->with(['race']);
    }

    public function clanMembership(): HasOne
    {
        return $this->hasOne(ClanMember::class);
    }
}
