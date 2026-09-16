<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LOCKPICKS = [
        ['Грубая отмычка', 1, 0, 0, 0, 'common'],
        ['Усиленная отмычка', 2, 5, 5, 3, 'uncommon'],
        ['Стальная отмычка', 3, 10, 10, 6, 'rare'],
        ['Точная отмычка', 4, 15, 15, 9, 'epic'],
        ['Мастерская отмычка', 5, 20, 20, 12, 'legendary'],
        ['Рунная отмычка', 6, 25, 25, 15, 'heroic'],
    ];

    public function up(): void
    {
        Schema::create('share_item_lockpick_configs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('share_item_id')->unique()->constrained('share_items')->cascadeOnDelete();
            $table->unsignedTinyInteger('tier');
            $table->unsignedTinyInteger('speed_bonus_percent')->default(0);
            $table->unsignedTinyInteger('failure_preserve_chance_percent')->default(0);
            $table->unsignedTinyInteger('trap_avoid_chance_percent')->default(0);
            $table->timestamps();
        });

        Schema::table('lockpicking_attempts', function (Blueprint $table): void {
            $table->foreignId('lockpick_share_item_id')->nullable()->after('share_item_id')->constrained('share_items')->nullOnDelete();
            $table->unsignedTinyInteger('lockpick_tier_snapshot')->default(1)->after('skill_snapshot');
            $table->unsignedTinyInteger('speed_bonus_snapshot')->default(0)->after('lockpick_tier_snapshot');
            $table->unsignedTinyInteger('failure_preserve_chance_snapshot')->default(0)->after('speed_bonus_snapshot');
            $table->unsignedTinyInteger('trap_avoid_chance_snapshot')->default(0)->after('failure_preserve_chance_snapshot');
        });

        Schema::table('lockpicking_logs', function (Blueprint $table): void {
            $table->foreignId('lockpick_share_item_id')->nullable()->after('share_item_id')->constrained('share_items')->nullOnDelete();
            $table->unsignedTinyInteger('lockpick_tier')->default(1)->after('skill_level');
            $table->boolean('lockpick_broken')->default(false)->after('trap_triggered');
            $table->boolean('trap_avoided')->default(false)->after('lockpick_broken');
        });

        $legacyId = DB::table('share_items')->where('name', 'Отмычка')->value('id');
        if ($legacyId !== null) {
            DB::table('share_items')->where('id', $legacyId)->update([
                'name' => 'Грубая отмычка',
                'description' => 'Простой расходный инструмент для замков I тира.',
                'rarity' => 'common',
                'is_lockpick' => true,
                'is_stackable' => true,
                'updated_at' => now(),
            ]);
        }

        foreach (self::LOCKPICKS as [$name, $tier, $speed, $preserve, $trapAvoid, $rarity]) {
            $itemId = DB::table('share_items')->where('name', $name)->value('id');
            $values = [
                'description' => sprintf('Отмычка %d тира. Скорость взлома +%d%%, шанс сохранить при неудаче %d%%, обход ловушки %d%%.', $tier, $speed, $preserve, $trapAvoid),
                'type' => 'resource',
                'rarity' => $rarity,
                'is_lockpick' => true,
                'is_stackable' => true,
                'is_active' => true,
                'is_sell' => true,
                'is_give' => true,
                'is_droppable' => true,
                'updated_at' => now(),
            ];
            if ($itemId === null) {
                $itemId = DB::table('share_items')->insertGetId($values + ['name' => $name, 'created_at' => now()]);
            } else {
                DB::table('share_items')->where('id', $itemId)->update($values);
            }
            DB::table('share_item_lockpick_configs')->updateOrInsert(
                ['share_item_id' => $itemId],
                [
                    'tier' => $tier,
                    'speed_bonus_percent' => $speed,
                    'failure_preserve_chance_percent' => $preserve,
                    'trap_avoid_chance_percent' => $trapAvoid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        Schema::table('lockpicking_logs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('lockpick_share_item_id');
            $table->dropColumn(['lockpick_tier', 'lockpick_broken', 'trap_avoided']);
        });
        Schema::table('lockpicking_attempts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('lockpick_share_item_id');
            $table->dropColumn(['lockpick_tier_snapshot', 'speed_bonus_snapshot', 'failure_preserve_chance_snapshot', 'trap_avoid_chance_snapshot']);
        });
        Schema::dropIfExists('share_item_lockpick_configs');
    }
};
