<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->foreignId('required_active_effect_id')->nullable()->after('upgrade_gold_cost')
                ->comment('Предмет выпадает с монстра только если у игрока активен этот эффект (buff)')
                ->constrained('effects')->nullOnDelete();
            $table->boolean('drop_direct_to_backpack')->default(false)->after('required_active_effect_id')
                ->comment('Выпадает прямо в рюкзак игрока, минуя землю локации');
        });
    }

    public function down(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('required_active_effect_id');
            $table->dropColumn('drop_direct_to_backpack');
        });
    }
};
