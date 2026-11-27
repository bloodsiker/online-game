<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Новый эффект предмета grant_premium — «Сфера премиума». */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $column = DB::selectOne('SHOW COLUMNS FROM share_item_effects WHERE Field = ?', ['effect_type']);
        if (str_starts_with($column->Type, 'enum(') && ! str_contains($column->Type, "'grant_premium'")) {
            // Сохраняем все существующие значения, в том числе добавленные другими фичами.
            $type = substr($column->Type, 0, -1).",'grant_premium')";
            $nullability = $column->Null === 'YES' ? 'NULL DEFAULT NULL' : 'NOT NULL';
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type {$type} {$nullability}");
        }
    }

    public function down(): void
    {
        DB::table('share_item_effects')->where('effect_type', 'grant_premium')->delete();
        // Значение enum оставляем: оно аддитивное, на него могут ссылаться предметы, настроенные в админке.
    }
};
