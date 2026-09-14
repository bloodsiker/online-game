<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_ITEM_NAME = 'Жезл Пепельной Башни';

    private const NEW_ITEM_NAME = 'Фолиант Пепельной Башни';

    private const OLD_SKILL_NAME = 'Владение жезлом';

    private const NEW_SKILL_NAME = 'Владение фолиантом';

    public function up(): void
    {
        DB::transaction(function (): void {
            $item = DB::table('share_items')
                ->whereIn('name', [self::OLD_ITEM_NAME, self::NEW_ITEM_NAME])
                ->first(['id', 'skill_id']);

            if ($item === null) {
                return;
            }

            DB::table('share_items')->where('id', $item->id)->update([
                'name' => self::NEW_ITEM_NAME,
                'description' => 'Одноручный древний фолиант Пепельной Башни. Усиливает магические критические удары и позволяет использовать щит.',
                'image' => '/main/images/folio-ashen-tower.png',
                'is_two_hand' => false,
                'updated_at' => now(),
            ]);

            DB::table('share_item_stats')
                ->where('share_item_id', $item->id)
                ->where('stat_type', 'attack_max')
                ->update(['value' => 2]);

            DB::table('share_item_stats')
                ->where('share_item_id', $item->id)
                ->where('stat_type', 'crit_damage')
                ->update(['stat_type' => 'magic_crit_damage']);

            if ($item->skill_id !== null) {
                DB::table('skills')->where('id', $item->skill_id)->update([
                    'name' => self::NEW_SKILL_NAME,
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $item = DB::table('share_items')
                ->whereIn('name', [self::NEW_ITEM_NAME, self::OLD_ITEM_NAME])
                ->first(['id', 'skill_id']);

            if ($item === null) {
                return;
            }

            DB::table('share_items')->where('id', $item->id)->update([
                'name' => self::OLD_ITEM_NAME,
                'description' => 'Короткий жезл с гранёным навершием — резкий, но не самый мощный разряд, который иногда бьёт в разы сильнее обычного.',
                'image' => 'items/e7NuQrgXegcbfXwDkrCNOUePuuipP4BQlMpySBL1.webp',
                'is_two_hand' => false,
                'updated_at' => now(),
            ]);

            DB::table('share_item_stats')
                ->where('share_item_id', $item->id)
                ->where('stat_type', 'attack_max')
                ->update(['value' => 3]);

            DB::table('share_item_stats')
                ->where('share_item_id', $item->id)
                ->where('stat_type', 'magic_crit_damage')
                ->update(['stat_type' => 'crit_damage']);

            if ($item->skill_id !== null) {
                DB::table('skills')->where('id', $item->skill_id)->update([
                    'name' => self::OLD_SKILL_NAME,
                    'updated_at' => now(),
                ]);
            }
        });
    }
};
