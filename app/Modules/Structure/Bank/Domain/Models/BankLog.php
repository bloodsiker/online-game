<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Models;

use App\Models\User;
use App\Modules\Structure\Bank\Domain\Enums\BankAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property int|null $related_user_id
 * @property BankAction $action
 * @property int $amount
 * @property int $balance_after
 */
class BankLog extends Model
{
    protected $fillable = ['user_id', 'related_user_id', 'action', 'amount', 'balance_after'];

    protected $casts = [
        'action' => BankAction::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }
}
