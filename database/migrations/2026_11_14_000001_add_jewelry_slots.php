<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'ring_1' => 'Кольцо 1', 'ring_2' => 'Кольцо 2', 'ring_3' => 'Кольцо 3',
        'ring_4' => 'Кольцо 4', 'ring_5' => 'Кольцо 5',
        'earring_1' => 'Серьга 1', 'earring_2' => 'Серьга 2',
        'necklace_1' => 'Ожерелье 1', 'necklace_2' => 'Ожерелье 2',
    ];

    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE share_items MODIFY slot ENUM('hand','helmet','shoulder','forearm','armor','legging','chain_armor','cloak','shoes','gloves','belt','bag','ring','earring','necklace') NULL");
        }

        Schema::table('player_equipments', function (Blueprint $table): void {
            foreach (self::COLUMNS as $column => $comment) {
                $table->foreignId($column)->nullable()->comment($comment)->constrained('items')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('player_equipments', function (Blueprint $table): void {
            foreach (array_keys(self::COLUMNS) as $column) {
                $table->dropConstrainedForeignId($column);
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE share_items MODIFY slot ENUM('hand','helmet','shoulder','forearm','armor','legging','chain_armor','cloak','shoes','gloves','belt','bag') NULL");
        }
    }
};
