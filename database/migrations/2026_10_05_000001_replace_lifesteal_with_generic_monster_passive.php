<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Заменяет узкоспециальный monsters.lifesteal_percent на универсальную
 * пару колонок passive_type/passive_value — зеркало уже существующего
 * share_items.innate_passive_type/innate_passive_value (встроенная
 * пассивка предмета без руны, см. PlayerRunePassiveService).
 *
 * Причина: lifesteal_percent решал только вампиризм — любая новая
 * пассивка монстра (оглушение, двойной удар, щит и т.п. — весь
 * RunePassiveType) потребовала бы ещё одной колонки. Эта пара колонок
 * переиспользует тот же RunePassiveType enum и подходит для любой его
 * будущей пассивки без новых миграций.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->string('passive_type', 20)->nullable()->after('aggression');
            $table->unsignedTinyInteger('passive_value')->nullable()->after('passive_type');
        });

        DB::table('monsters')
            ->where('lifesteal_percent', '>', 0)
            ->update([
                'passive_type' => 'vampirism',
                'passive_value' => DB::raw('lifesteal_percent'),
            ]);

        Schema::table('monsters', function (Blueprint $table): void {
            $table->dropColumn('lifesteal_percent');
        });
    }

    public function down(): void
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->unsignedTinyInteger('lifesteal_percent')->default(0)->after('aggression');
        });

        DB::table('monsters')
            ->where('passive_type', 'vampirism')
            ->update(['lifesteal_percent' => DB::raw('passive_value')]);

        Schema::table('monsters', function (Blueprint $table): void {
            $table->dropColumn(['passive_type', 'passive_value']);
        });
    }
};
