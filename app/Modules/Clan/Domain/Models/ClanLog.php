<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Models;

use App\Modules\Clan\Domain\Enums\ClanLogAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ClanLogAction $action
 * @property string $details
 *
 * @property-read User $user
 * @property-read Clan $clan
 */
class ClanLog extends Model
{
    use HasFactory;

    protected $fillable = ['action', 'clan_id', 'user_id', 'details'];

    protected $casts = [
        'action' => ClanLogAction::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }
}