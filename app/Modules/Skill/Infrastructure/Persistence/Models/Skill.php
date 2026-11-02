<?php

declare(strict_types=1);

namespace App\Modules\Skill\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;
    use HasLegacyTranslations;

    public $translatable = ['name', 'description'];

    public const SKILL_HAND_ID = 1;

    /** «Колдовство» — навык, усиливающий силу заклинаний и снижающий их стоимость маны, см. HitCalculator::weaponSkillMultiplier(). */
    public const SKILL_MAGIC_ID = 27;

    protected $fillable = ['name', 'description', 'type'];
}
