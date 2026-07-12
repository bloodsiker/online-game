<?php

declare(strict_types=1);

namespace App\Modules\Race\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    use HasFactory;

    protected $fillable = ['strength', 'agility', 'intuition', 'wisdom', 'intelligence', 'endurance', 'free_stats'];
}
