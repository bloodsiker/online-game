<?php

declare(strict_types=1);

namespace App\Modules\Premium\Infrastructure\Persistence\Models;

use App\Modules\Premium\Domain\Enums\PremiumBonus;
use Illuminate\Database\Eloquent\Model;

final class PremiumLevel extends Model
{
    public function bonusEnabled(PremiumBonus $bonus): bool
    {
        return (bool) ($this->bonuses['_enabled'][$bonus->value] ?? true);
    }

    protected $primaryKey = 'level';

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = ['bonuses' => 'array', 'required_diamond_cents' => 'integer'];
}
