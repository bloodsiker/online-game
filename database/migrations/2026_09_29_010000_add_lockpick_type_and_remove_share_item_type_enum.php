<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Новые типы игровых предметов не должны требовать ALTER TABLE для
        // изменения SQL ENUM. Валидация допустимых системных типов остаётся в
        // PHP enum ShareItemType, а БД хранит расширяемое строковое значение.
        DB::statement("ALTER TABLE `share_items` MODIFY `type` VARCHAR(32) NOT NULL DEFAULT 'resource'");

        $lockpickIds = DB::table('share_item_lockpick_configs')->select('share_item_id');

        DB::table('share_items')
            ->where('is_lockpick', true)
            ->orWhereIn('id', $lockpickIds)
            ->update([
                'type' => 'lockpick',
                'is_lockpick' => true,
                'is_stackable' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Откат к ENUM небезопасен: после этой миграции могут появиться новые
        // типы предметов. Схема остаётся строковой, а данные не теряются.
    }
};
