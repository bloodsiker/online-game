<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShareAction extends Model
{
    use HasFactory, HasLegacyTranslations;

    public $translatable = ['name'];

    protected $fillable = ['alias', 'name'];

    public function structures(): BelongsToMany
    {
        return $this->belongsToMany(Structure::class, 'structure_actions', 'share_action_id', 'structure_id')->withPivot('is_active');
    }
}
