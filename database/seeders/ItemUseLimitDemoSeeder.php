<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemBuff;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItemUseLimit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Демо-конфигурация для механики «ограничение использования предмета»
 * (см. ItemUsagePolicyService): «Кровавый эликсир Братства» — не чаще
 * 2 раз в сутки, а его бафф нельзя повторно накладывать, пока уже активен.
 * Раньше жила прямо в миграции 2026_09_13_000001 — вынесено сюда, чтобы
 * схемная миграция не зависела от наличия конкретного игрового предмета.
 */
class ItemUseLimitDemoSeeder extends Seeder
{
    public function run(): void
    {
        $item = ShareItem::where('name', 'Кровавый эликсир Братства')->first();
        if ($item === null) {
            return;
        }

        DB::transaction(function () use ($item): void {
            ShareItemUseLimit::query()->updateOrCreate(
                ['share_item_id' => $item->id],
                ['max_uses' => 2, 'period_seconds' => 86400],
            );

            $effectId = DB::table('effects')->where('slug', 'blood_surge')->value('id');
            if ($effectId !== null) {
                ShareItemBuff::where('share_item_id', $item->id)
                    ->where('effect_id', $effectId)
                    ->update(['reapply_policy' => 'block']);
            }
        });
    }
}
