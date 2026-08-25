<?php

declare(strict_types=1);

namespace App\Modules\Monster\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonsterEffect extends Model
{
    use HasFactory;

    public $table = 'monster_effects';

    protected $fillable = ['monster_id', 'effect_id', 'chance', 'duration_seconds', 'power_percent', 'trigger_on_hit'];

    protected $casts = [
        'chance' => 'float',
        'duration_seconds' => 'integer',
        'power_percent' => 'float',
        'trigger_on_hit' => 'boolean',
    ];
}
