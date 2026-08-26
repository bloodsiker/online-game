<?php

declare(strict_types=1);

namespace App\Modules\Party\Infrastructure\Persistence\Models;

use App\Modules\Party\Domain\Enums\PartyStatus;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $leader_user_id
 * @property string $invite_code
 * @property int $max_players
 * @property PartyStatus $status
 * @property-read User           $leader
 * @property-read Collection<PartyMember> $members
 */
class Party extends Model
{
    protected $fillable = ['leader_user_id', 'invite_code', 'max_players', 'status'];

    protected $casts = [
        'status' => PartyStatus::class,
    ];

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(PartyMember::class);
    }

    public function isLeader(int $userId): bool
    {
        return (int) $this->leader_user_id === $userId;
    }

    public function isFull(): bool
    {
        return $this->members()->count() >= $this->max_players;
    }

    public function isOpen(): bool
    {
        return $this->status === PartyStatus::OPEN;
    }

    public function allReady(): bool
    {
        return $this->members()->where('is_ready', false)->doesntExist();
    }
}
