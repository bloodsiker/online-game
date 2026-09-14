<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reputations', function (Blueprint $table): void {
            $table->string('favor_effect_type', 20)->nullable()->after('icon')
                ->comment('heal|poison|attack_buff — эффект божественной милости, см. DivineFavorService');
            $table->unsignedTinyInteger('favor_proc_chance')->nullable()->after('favor_effect_type')
                ->comment('Шанс % сработать в бою за хит/ход');
            $table->foreignId('elixir_share_item_id')->nullable()->after('favor_proc_chance')
                ->comment('Эликсир, выдаваемый за каждое подношение')
                ->constrained('share_items')->nullOnDelete();
            $table->foreignId('favor_marker_effect_id')->nullable()->after('elixir_share_item_id')
                ->comment('Effect, чьё присутствие у игрока (от эликсира) сигналит "милость активна"')
                ->constrained('effects')->nullOnDelete();
        });

        Schema::table('reputation_tiers', function (Blueprint $table): void {
            $table->unsignedTinyInteger('favor_percent')->nullable()->after('max_points')
                ->comment('Величина % эффекта божественной милости на этом тире');
            $table->unsignedTinyInteger('feat_favor_percent')->nullable()->after('favor_percent')
                ->comment('Величина % эффекта, когда подвиг выполнен (эффект становится постоянным)');
        });
    }

    public function down(): void
    {
        Schema::table('reputations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('elixir_share_item_id');
            $table->dropConstrainedForeignId('favor_marker_effect_id');
            $table->dropColumn(['favor_effect_type', 'favor_proc_chance']);
        });

        Schema::table('reputation_tiers', function (Blueprint $table): void {
            $table->dropColumn(['favor_percent', 'feat_favor_percent']);
        });
    }
};
