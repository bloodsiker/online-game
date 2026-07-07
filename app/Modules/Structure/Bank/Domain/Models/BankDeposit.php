<?php

declare(strict_types=1);

namespace App\Modules\Structure\Bank\Domain\Models;

use App\Modules\User\Infrastructure\Persistence\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property float $percent  процент в день
 * @property int $term_days
 * @property Carbon $matures_at
 * @property Carbon|null $closed_at
 * @property Carbon $created_at
 */
class BankDeposit extends Model
{
    protected $fillable = ['user_id', 'amount', 'percent', 'term_days', 'matures_at', 'closed_at'];

    protected $casts = [
        'percent' => 'float',
        'matures_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isMatured(): bool
    {
        return $this->matures_at->isPast();
    }

    public function payout(): int
    {
        return $this->amount + $this->interest();
    }

    public function interest(): int
    {
        return (int) floor($this->amount * $this->percent / 100 * $this->term_days);
    }

    public function accruedInterest(): int
    {
        $daysElapsed = min((int) $this->created_at->diffInDays(Carbon::now()), $this->term_days);

        return (int) floor($this->amount * $this->percent / 100 * $daysElapsed);
    }
}