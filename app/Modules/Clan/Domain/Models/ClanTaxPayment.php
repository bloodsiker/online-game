<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Models;

use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClanTaxPayment extends Model
{
    protected $fillable = [
        'clan_id',
        'user_id',
        'structure_id',
        'amount',
        'balance_before',
        'balance_after',
        'paid_from',
        'paid_until',
    ];

    protected $casts = [
        'paid_from' => 'datetime',
        'paid_until' => 'datetime',
    ];

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function structure(): BelongsTo
    {
        return $this->belongsTo(Structure::class);
    }
}
