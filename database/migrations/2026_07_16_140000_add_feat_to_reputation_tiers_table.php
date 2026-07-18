<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Подвиг для медали: если у тира заполнен feat_quest_id, медаль выдаётся
     * только при достижении очков тира И выполнении квеста-подвига.
     * Для цепочки квестов feat_quest_id указывает на ФИНАЛЬНЫЙ квест цепочки
     * (порядок внутри цепочки задаётся через quests.after_quest_id).
     */
    public function up(): void
    {
        Schema::table('reputation_tiers', function (Blueprint $table) {
            $table->foreignId('feat_quest_id')->nullable()->after('medal_icon')
                ->constrained('quests')->nullOnDelete();
            $table->text('feat_description')->nullable()->after('feat_quest_id');
        });
    }

    public function down(): void
    {
        Schema::table('reputation_tiers', function (Blueprint $table) {
            $table->dropForeign(['feat_quest_id']);
            $table->dropColumn(['feat_quest_id', 'feat_description']);
        });
    }
};
