<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE share_items MODIFY slot ENUM('hand','helmet','shoulder','forearm','armor','legging','chain_armor','cloak','shoes','gloves','belt','bag','ring','earring','necklace','quiver') NULL");
        }

        Schema::table('player_equipments', function (Blueprint $table): void {
            $table->foreignId('quiver')->nullable()->comment('Колчан')->constrained('items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('player_equipments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('quiver');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE share_items MODIFY slot ENUM('hand','helmet','shoulder','forearm','armor','legging','chain_armor','cloak','shoes','gloves','belt','bag','ring','earring','necklace') NULL");
        }
    }
};
