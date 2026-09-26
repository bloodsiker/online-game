<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_stages', function (Blueprint $table) {
            // Предмет, который NPC физически вручает игроку в рюкзак в момент завершения
            // этого этапа (напр. "кузнец отдаёт готовый ключ") — отдельно от финальных
            // наград квеста и от deliver-целей (те требуют отдать предмет NPC, а не получить).
            $table->foreignId('grant_share_item_id')->nullable()->after('ready_text')->constrained('share_items')->nullOnDelete();
            $table->unsignedInteger('grant_amount')->default(1)->after('grant_share_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('quest_stages', function (Blueprint $table) {
            $table->dropForeign(['grant_share_item_id']);
            $table->dropColumn(['grant_share_item_id', 'grant_amount']);
        });
    }
};
