<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Infrastructure\Persistence\Models;

use App\Models\Skill;
use App\Modules\MagicSkill\Domain\Enums\MagicSkillRequirementType;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $magic_skill_id
 * @property MagicSkillRequirementType $type
 * @property string|null $stat_key
 * @property int|null $skill_id
 * @property int $min_value
 * @property-read Skill|null $skill
 */
class MagicSkillRequirement extends Model
{
    protected $fillable = ['magic_skill_id', 'type', 'stat_key', 'skill_id', 'min_value'];

    protected $casts = [
        'type' => MagicSkillRequirementType::class,
    ];

    public function magicSkill(): BelongsTo
    {
        return $this->belongsTo(MagicSkill::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function label(): string
    {
        return match ($this->type) {
            MagicSkillRequirementType::LEVEL => 'Уровень персонажа',
            MagicSkillRequirementType::STAT => $this->statLabel(),
            MagicSkillRequirementType::SKILL => $this->skill?->name ?? 'Навык #'.$this->skill_id,
        };
    }

    private function statLabel(): string
    {
        $key = PlayerStatKey::tryFrom((string) $this->stat_key);

        return $key?->label() ?? (string) $this->stat_key;
    }
}
