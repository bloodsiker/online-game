<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CERTIFICATES = [287 => 10, 288 => 50, 289 => 100, 290 => 250, 291 => 500];

    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $column = DB::selectOne('SHOW COLUMNS FROM share_item_effects WHERE Field = ?', ['effect_type']);
            if (str_starts_with($column->Type, 'enum(') && ! str_contains($column->Type, "'grant_diamonds'")) {
                // Preserve every existing type, including types added by other features.
                $type = substr($column->Type, 0, -1).",'grant_diamonds')";
                $nullability = $column->Null === 'YES' ? 'NULL DEFAULT NULL' : 'NOT NULL';
                DB::statement("ALTER TABLE share_item_effects MODIFY effect_type {$type} {$nullability}");
            }
        }

        DB::transaction(function (): void {
            foreach (self::CERTIFICATES as $id => $amount) {
                $item = DB::table('share_items')->where('id', $id)->first();
                if ($item === null) {
                    continue;
                }
                $name = json_decode($item->name, true);
                $name = is_array($name) ? ($name['ru'] ?? '') : $item->name;
                if ($name !== 'Сертификат «'.$amount.' Бриллиантов»') {
                    throw new RuntimeException('Unexpected diamond certificate at share_items.id='.$id);
                }

                DB::table('share_items')->where('id', $id)->update(['is_use' => true, 'is_slot_usable' => false, 'count_use' => 0, 'updated_at' => now()]);
                DB::table('share_item_effects')->updateOrInsert(
                    ['share_item_id' => $id, 'effect_type' => 'grant_diamonds'],
                    ['value' => $amount, 'value_type' => 'flat', 'duration_seconds' => null, 'created_at' => now(), 'updated_at' => now()],
                );
            }
        });
    }

    public function down(): void
    {
        DB::table('share_item_effects')->whereIn('share_item_id', array_keys(self::CERTIFICATES))
            ->where('effect_type', 'grant_diamonds')->delete();
        // Keep the additive enum value and item flags: other administrator-configured items may use them.
    }
};
