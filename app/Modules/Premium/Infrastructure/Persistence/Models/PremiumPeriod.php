<?php

declare(strict_types=1);

namespace App\Modules\Premium\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Model;

final class PremiumPeriod extends Model
{
    protected $guarded = [];

    protected $casts = ['started_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'diamond_cents' => 'integer'];
}
