<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $share_item_id
 * @property ShareItemRequirementType $type
 * @property string|null $stat_key
 * @property int|null $skill_id
 * @property int $min_value
 * @property-read Skill|null $skill
 */
class ShareItemRequirement extends Model
{
    protected $fillable = ['share_item_id', 'type', 'stat_key', 'skill_id', 'min_value'];

    protected $casts = [
        'type' => ShareItemRequirementType::class,
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class, 'share_item_id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function label(): string
    {
        return match ($this->type) {
            ShareItemRequirementType::LEVEL => 'Уровень персонажа',
            ShareItemRequirementType::STAT => $this->statLabel(),
            ShareItemRequirementType::SKILL => $this->skill?->name ?? 'Навык #'.$this->skill_id,
        };
    }

    private function statLabel(): string
    {
        $key = PlayerStatKey::tryFrom((string) $this->stat_key);

        return $key?->label() ?? (string) $this->stat_key;
    }
}
