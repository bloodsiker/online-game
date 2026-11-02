<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ранги поддерживающих скиллов (хил/баффы, см. 2026_10_27_000001) были
 * отдельными magic_skills без связи друг с другом — изучение «Каменная
 * кожа III» не убирало у игрока «Каменная кожа II», оба оставались
 * выученными и занимали слот одновременно. upgrades_skill_id указывает на
 * ПРЕДЫДУЩИЙ ранг той же ветки — LearnMagicSkillFromBook использует это,
 * чтобы заменить старый ранг новым, а не копить оба.
 *
 * Боевые скиллы (Молния/Огненный залп/...) сюда не входят — это осознанно
 * РАЗНЫЕ заклинания (не реСкин одного), держатся в арсенале одновременно.
 */
return new class extends Migration
{
    /** @var array<int, int> новый ранг → предыдущий ранг той же ветки */
    private const UPGRADE_CHAINS = [
        80 => 27, // Среднее исцеление ← Малое исцеление
        28 => 80, // Великое исцеление ← Среднее исцеление
        81 => 24, // Боевой клич II ← I
        82 => 81, // Боевой клич III ← II
        83 => 25, // Каменная кожа II ← I
        84 => 83, // Каменная кожа III ← II
        85 => 26, // Благословение II ← I
        86 => 85, // Благословение III ← II
        87 => 29, // Регенерация II ← I
        88 => 87, // Регенерация III ← II
    ];

    public function up(): void
    {
        Schema::table('magic_skills', function (Blueprint $table) {
            $table->unsignedBigInteger('upgrades_skill_id')->nullable()->after('skill_id');
        });

        foreach (self::UPGRADE_CHAINS as $newId => $previousId) {
            DB::table('magic_skills')->where('id', $newId)->update(['upgrades_skill_id' => $previousId]);
        }
    }

    public function down(): void
    {
        Schema::table('magic_skills', function (Blueprint $table) {
            $table->dropColumn('upgrades_skill_id');
        });
    }
};
