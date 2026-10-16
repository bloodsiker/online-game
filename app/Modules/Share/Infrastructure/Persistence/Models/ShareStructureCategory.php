<?php

declare(strict_types=1);

namespace App\Modules\Share\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareStructureCategory extends Model
{
    use HasFactory, HasLegacyTranslations;

    public $translatable = ['name'];

    protected $table = 'share_structure_categories';

    protected $fillable = ['name', 'is_active'];

    protected $attributes = [
        'is_active' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
