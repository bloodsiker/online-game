<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SKILL_NAME = 'Взломщик';

    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->boolean('is_lockpick')->default(false)->after('skill_exp')->index();
        });

        Schema::create('share_item_lock_configs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete()->unique();
            $table->unsignedSmallInteger('lock_required_skill')->default(0);
            $table->unsignedSmallInteger('lock_duration_seconds')->default(12);
            $table->unsignedTinyInteger('trap_level')->default(0);
            $table->foreignId('trap_effect_id')->nullable()->constrained('effects')->nullOnDelete();
            $table->unsignedSmallInteger('trap_effect_duration_seconds')->default(60);
            $table->unsignedTinyInteger('trap_damage_percent')->default(0);
            $table->timestamps();
        });

        Schema::create('lockpicking_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete()->unique();
            // Без FK на конкретный экземпляр: после победы первого игрока
            // попытки конкурентов должны дожить до ответа «сундук уже открыт».
            $table->unsignedBigInteger('item_id');
            $table->foreignId('share_item_id')->nullable()->constrained('share_items')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->boolean('is_inventory');
            $table->unsignedSmallInteger('skill_snapshot');
            $table->decimal('chance_snapshot', 5, 2);
            $table->timestamp('started_at');
            $table->timestamp('completes_at');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['item_id', 'completes_at']);
        });

        Schema::create('lockpicking_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->foreignId('share_item_id')->nullable()->constrained('share_items')->nullOnDelete();
            $table->enum('source', ['world', 'inventory']);
            $table->enum('result', ['success', 'failure', 'cancelled', 'taken']);
            $table->unsignedSmallInteger('skill_level');
            $table->decimal('success_chance', 5, 2);
            $table->boolean('trap_triggered')->default(false);
            $table->timestamps();

            $table->index(['player_id', 'created_at']);
            $table->index(['item_id', 'created_at']);
        });

        $skillId = DB::table('skills')->where('name', self::SKILL_NAME)->value('id');
        if ($skillId === null) {
            $skillId = DB::table('skills')->insertGetId([
                'name' => self::SKILL_NAME,
                'type' => 'peaceful',
                'description' => 'Открывает замки на сундуках и шкатулках, обезвреживая установленные на них ловушки.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! DB::table('skill_level_requirements')->where('skill_id', $skillId)->exists()) {
            $rows = [];
            $previous = 0;
            for ($level = 1; $level <= 300; $level++) {
                $experience = (int) round(100 + (1_100_000 - 100) * (($level - 1) / 299) ** 1.6);
                $rows[] = [
                    'skill_id' => $skillId,
                    'lvl' => $level,
                    'exp_required' => $experience,
                    'exp_diff' => $experience - $previous,
                ];
                $previous = $experience;
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('skill_level_requirements')->insert($chunk);
            }
        }

        $lockpick = DB::table('share_items')->where('name', 'Отмычка');
        if ($lockpick->exists()) {
            $lockpick->update(['is_lockpick' => true, 'is_stackable' => true, 'updated_at' => now()]);
        } else {
            DB::table('share_items')->insert([
                'name' => 'Отмычка',
                'description' => 'Простой расходный инструмент для вскрытия замков. Ломается при неудачной попытке.',
                'type' => 'resource',
                'is_lockpick' => true,
                'is_stackable' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lockpicking_logs');
        Schema::dropIfExists('lockpicking_attempts');
        Schema::dropIfExists('share_item_lock_configs');

        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropIndex(['is_lockpick']);
            $table->dropColumn('is_lockpick');
        });
    }
};
