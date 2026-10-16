<?php

declare(strict_types=1);

namespace App\Modules\Trade\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Localization\HasLegacyTranslations;
use Illuminate\Database\Eloquent\Model;

class TradePost extends Model
{
    use HasLegacyTranslations;

    protected $table = 'trade_posts';

    public array $translatable = ['name'];

    protected $fillable = ['npc_id', 'name', 'license_price', 'is_active'];
}
