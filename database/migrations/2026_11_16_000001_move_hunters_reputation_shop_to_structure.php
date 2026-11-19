<?php

declare(strict_types=1);

use App\Modules\Reputation\Application\Services\MoveReputationShopToStructure;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('reputations')->where('id', 2)->exists()) {
            return;
        }

        app(MoveReputationShopToStructure::class)->execute(
            reputationId: 2,
            nameRu: 'Магазин Ордена Охотников',
            nameUk: 'Магазин Ордену Мисливців',
            descriptionRu: '<p>Снаряжение и припасы Ордена Охотников.</p>',
            descriptionUk: '<p>Спорядження та припаси Ордену Мисливців.</p>',
        );
    }

    public function down(): void
    {
        // Перенос контента не откатываем: после него магазин может получить новые товары и покупки.
    }
};
