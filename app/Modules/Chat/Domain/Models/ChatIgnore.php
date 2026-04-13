<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $ignored_user_id
 * @property-read User $user
 * @property-read User $ignoredUser
 */
class ChatIgnore extends Model
{
    protected $fillable = ['user_id', 'ignored_user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ignoredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ignored_user_id');
    }
}