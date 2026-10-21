<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Переделка «Благословения Новичка» (id 1833, см.
 * 2026_10_17_000004_seed_starter_legendary_artifact_pendants.php) — по
 * прямому указанию пользователя: новое название «Печать Пробуждения» и
 * широкий разброс статов «всего понемногу» (armor/dodge/critical/
 * attack_min/attack_max/hp_max/magic_attack/wisdom/intelligence) вместо
 * узкого боевого набора (attack_min/max + critical), чтобы облегчить старт
 * игры независимо от того, в какой билд игрок решит вложиться (воин, маг,
 * танк) — предмет ещё не предполагает выбора, игрок только начал.
 *
 * Значения подобраны скромно (3-10 за стат — меньше, чем в узком варианте),
 * бюджет расходится по девяти статам вместо трёх, сохраняя тот же принцип
 * «огромно на фоне стартовых характеристик, но естественно растворяется
 * к позднему левелингу» (см. докблок прошлой миграции).
 */
return new class extends Migration
{
    private const ITEM_ID = 1833;

    private const OLD_NAME = ['ru' => 'Благословение Новичка', 'uk' => 'Благословення Новачка'];

    private const OLD_DESCRIPTION = [
        'ru' => 'Простой оберег, что вручают каждому, кто впервые переступает порог таверны, — говорят, удача странника живёт в нём до тех пор, пока он сам не станет легендой.',
        'uk' => 'Простий оберіг, що вручають кожному, хто вперше переступає поріг таверни, — кажуть, удача мандрівника живе в ньому доти, доки він сам не стане легендою.',
    ];

    private const OLD_STATS = [
        ['type' => 'attack_min', 'value' => 5],
        ['type' => 'attack_max', 'value' => 8],
        ['type' => 'critical', 'value' => 10],
    ];

    private const NEW_NAME = ['ru' => 'Печать Пробуждения', 'uk' => 'Печатка Пробудження'];

    private const NEW_DESCRIPTION = [
        'ru' => 'Простая печать, что вручают каждому пробудившемуся в этом мире, — равномерно укрепляет тело и разум, пока странник сам не решит, каким путём пойти дальше.',
        'uk' => 'Проста печатка, що вручають кожному пробудженому в цьому світі, — рівномірно зміцнює тіло й розум, доки мандрівник сам не вирішить, яким шляхом піти далі.',
    ];

    private const NEW_STATS = [
        ['type' => 'armor', 'value' => 8],
        ['type' => 'dodge', 'value' => 8],
        ['type' => 'critical', 'value' => 8],
        ['type' => 'attack_min', 'value' => 3],
        ['type' => 'attack_max', 'value' => 5],
        ['type' => 'hp_max', 'value' => 30],
        ['type' => 'magic_attack', 'value' => 10],
        ['type' => 'wisdom', 'value' => 5],
        ['type' => 'intelligence', 'value' => 5],
    ];

    public function up(): void
    {
        $now = now();

        DB::table('share_items')->where('id', self::ITEM_ID)->update([
            'name' => json_encode(self::NEW_NAME, JSON_UNESCAPED_UNICODE),
            'description' => json_encode(self::NEW_DESCRIPTION, JSON_UNESCAPED_UNICODE),
            'updated_at' => $now,
        ]);

        DB::table('share_item_stats')->where('share_item_id', self::ITEM_ID)->delete();

        foreach (self::NEW_STATS as $stat) {
            DB::table('share_item_stats')->insert([
                'share_item_id' => self::ITEM_ID,
                'stat_type' => $stat['type'],
                'value' => $stat['value'],
                'value_type' => 'flat',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $now = now();

        DB::table('share_items')->where('id', self::ITEM_ID)->update([
            'name' => json_encode(self::OLD_NAME, JSON_UNESCAPED_UNICODE),
            'description' => json_encode(self::OLD_DESCRIPTION, JSON_UNESCAPED_UNICODE),
            'updated_at' => $now,
        ]);

        DB::table('share_item_stats')->where('share_item_id', self::ITEM_ID)->delete();

        foreach (self::OLD_STATS as $stat) {
            DB::table('share_item_stats')->insert([
                'share_item_id' => self::ITEM_ID,
                'stat_type' => $stat['type'],
                'value' => $stat['value'],
                'value_type' => 'flat',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
