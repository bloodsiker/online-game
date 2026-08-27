<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ClanLevel extends Model
{
    protected $fillable = ['level', 'experience_required'];

    protected $casts = [
        'level' => 'integer',
        'experience_required' => 'decimal:2',
    ];
}
