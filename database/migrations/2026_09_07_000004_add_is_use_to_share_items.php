<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Пункт «Использовать» в рюкзаке показывается по белому списку типов
 * (potion/eat/scroll/artifact/chest/gift, см. bag.blade.php) — предмет с
 * инстант-эффектом, но другим типом (например misc), пункт не получал.
 * is_use — явный фолбэк: если true, пункт показывается независимо от типа.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->boolean('is_use')->default(false)->after('is_slot_usable');
        });

        // «Сфера возрождения» — тип сейчас misc (сменили в админке), но
        // предмет использует эффект restore_lost_exp и должен оставаться
        // используемым.
        DB::table('share_items')
            ->where('name', 'Сфера возрождения')
            ->update(['is_use' => true]);
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropColumn('is_use');
        });
    }
};
