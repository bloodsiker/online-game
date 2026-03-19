<?php

namespace App\Models\Player;

use App\Models\Location\Location;
use App\Models\Quest\Quest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerLocationAccess extends Model
{
    protected $table = 'player_location_access';

    protected $fillable = ['player_id', 'location_id', 'quest_id'];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }
}
