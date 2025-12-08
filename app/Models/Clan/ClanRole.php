<?php

namespace app\Models\Clan;

use App\Enums\QuestType;
use App\Models\Npc;
use App\Models\Quest\QuestObjective;
use App\Models\Quest\QuestReward;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClanRole extends Model
{
    use HasFactory;
}
