<?php

declare(strict_types=1);

namespace App\Modules\Race\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    use HasFactory, HasLegacyTranslations;

    public $translatable = ['name'];

    protected $fillable = ['name', 'strength', 'agility', 'intuition', 'wisdom', 'intelligence', 'endurance', 'free_stats'];

    public function avatars(): HasMany
    {
        return $this->hasMany(RaceAvatar::class)->orderBy('sort_order')->orderBy('id');
    }
}
