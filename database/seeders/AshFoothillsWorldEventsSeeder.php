<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use App\Modules\Share\Infrastructure\Localization\LocalizedItemSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 2 мировых события по прямому указанию пользователя — по образцу уже
 * существующих world_events (см. «Слёзы высохшей реки» для Опустевшей
 * Долины, «Дым и цепи катакомб» для Катакомб): каждое состоит из
 * world_events + world_event_stages (1 этап) + world_event_stage_targets.
 *
 *   - «Остывшие Угли Пожарища» (collect, map_id=16 «Пепельные
 *     Предгорья») — новый ресурс «Остывший Уголёк Пожарища».
 *   - «Тлеющий Страж Глубин» (kill, map_id=17 «Пепельные Глубины») —
 *     новый уникальный монстр-страж (lvl50, НЕ связан с обычным
 *     респауном зоны — как и у прочих event-монстров типа «Кадильный
 *     Дым»/«Метельный Дух», стоит только в этом событии).
 *     Архетип монстра: hpMultiplier=1.1, mitigation=15% (тяжёлый
 *     каменный страж), crit=12%, dodge=0, dmg=12% от HP игрока,
 *     magic_resistance=45 — посчитано через MonsterStatFormulas.
 *
 * map_influence_levels (лесенка репутации/наград за влияние) для карт
 * 16/17 сознательно НЕ создаётся — это отдельная подсистема наград,
 * пользователь просил только сами события и монстра.
 */
class AshFoothillsWorldEventsSeeder extends Seeder
{
    public function run(): void
    {
        if (LocalizedContentSeederQuery::forTable('world_events')->where('title', 'Остывшие Угли Пожарища')->exists()) {
            $this->command?->info('AshFoothillsWorldEventsSeeder: уже существует, пропускаю.');

            return;
        }

        $now = now();
        $nextStartAt = '2026-10-07 16:02:01'; // синхронизировано с остальными активными событиями

        // --- "Остывшие Угли Пожарища" (collect) ---

        $emberItemId = LocalizedItemSeederQuery::items()->insertGetId([
            'type' => 'resource',
            'name' => 'Остывший Уголёк Пожарища',
            'description' => 'Плотный, почти каменный уголь, который до сих пор хранит слабое тепло — след давнего низового пожара, выжегшего Пепельные Предгорья.',
            'image' => null,
            'is_two_hand' => 0,
            'count_use' => 0,
            'is_active' => 1,
            'is_sell' => 0,
            'is_auction_sellable' => 0,
            'is_give' => 0,
            'is_clan_warehouse_allowed' => 0,
            'is_slot_usable' => 0,
            'is_droppable' => 1,
            'is_stackable' => 1,
            'is_use' => 0,
            'is_weight' => 0,
            'price' => 0,
            'break_crystal' => 0,
            'rarity' => 'epic',
            'upgrade_gold_cost' => 0,
            'drop_direct_to_backpack' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $emberEventDescription = '<p>По всей ширине Пепельных Предгорий лежит пепел давнего низового пожара, выжегшего эти склоны до самого камня — но после особенно сильных порывов ветра среди серой пыли то здесь, то там обнажаются странные тёмные комья, всё ещё тёплые на ощупь, будто огонь внутри них так и не погас. Старатели зовут их Остывшими Углями Пожарища и уверяют, что ветер специально выдувает их на поверхность.</p><p>Уголь этот не похож на обычную золу: он плотный, почти каменный, и хранит жар даже спустя годы — ремесленники охотно платят за такую находку. Но стоит замешкаться: следующий же порыв ветра снова заносит угли пеплом, и тогда их уже не найти. Соберите Остывшие Угли Пожарища, пока ветер не спрятал их снова.</p>';

        $emberEventId = LocalizedContentSeederQuery::forTable('world_events')->insertGetId([
            'title' => 'Остывшие Угли Пожарища',
            'description' => $emberEventDescription,
            'objective_type' => 'collect',
            'image' => null,
            'map_id' => 16,
            'influence_map_id' => 16,
            'share_item_id' => $emberItemId,
            'monster_id' => null,
            'influence_name' => 'Пепельные Предгорья',
            'next_start_at' => $nextStartAt,
            'repeat_interval_minutes' => 240,
            'duration_minutes' => 60,
            'global_limit' => 50,
            'player_limit' => 5,
            'spawn_limit' => 10,
            'respawn_seconds' => 300,
            'item_lifetime_minutes' => 1440,
            'influence_per_item' => 1,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $emberStageId = LocalizedContentSeederQuery::forTable('world_event_stages')->insertGetId([
            'world_event_id' => $emberEventId,
            'position' => 1,
            'title' => 'Остывший Уголёк Пожарища',
            'objective_type' => 'collect',
            'share_item_id' => $emberItemId,
            'monster_id' => null,
            'global_limit' => 50,
            'player_limit' => 5,
            'spawn_limit' => 10,
            'respawn_seconds' => 300,
            'item_lifetime_minutes' => 1440,
            'influence_per_item' => 1,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('world_event_stage_targets')->insert([
            'world_event_stage_id' => $emberStageId,
            'share_item_id' => $emberItemId,
            'monster_id' => null,
            'position' => 1,
            'spawn_weight' => 100,
            'max_active' => null,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // --- "Тлеющий Страж Глубин" (kill) ---

        $guardianMonsterId = LocalizedContentSeederQuery::forTable('monsters')->insertGetId([
            'name' => 'Тлеющий Страж Глубин',
            'description' => 'Древний страж, сложенный из оплавленного камня и обугленной кости, — жар давнего пожара так и не угас внутри него, тлея столетиями в самом сердце Пепельных Глубин. Он не сходит с места, пока кто-то не потревожит тишину туннеля.',
            'image' => null,
            'lvl' => 50,
            'hp' => 442,
            'armor' => 162,
            'dodge' => 0,
            'critical' => 25,
            'min_dmg' => 50,
            'max_dmg' => 93,
            'attack_type' => 'physical',
            'magic_attack' => 0,
            'magic_power_coefficient' => 0,
            'magic_resistance' => 45,
            'aggression' => 90,
            'passive_type' => null,
            'passive_value' => null,
            'exp' => 4597,
            'min_money' => 0,
            'max_money' => 0,
            'is_boss' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $guardianEventDescription = '<p>В самом сердце Пепельных Глубин, там, где туннель сужается перед очередным разломом, стоит нечто, что путники издалека принимают за обвалившуюся колонну. Это Тлеющий Страж Глубин — древняя фигура из оплавленного камня и обугленной кости, в пустых глазницах которой до сих пор рдеет тусклый жар давнего пожара. Он не шевелится веками, пока кто-то неосторожный не подойдёт слишком близко.</p><p>Тогда камень трогается с места — медленно, но неотвратимо, а воздух вокруг начинает дрожать от нестерпимого жара. Те немногие, кто возвращался назад, рассказывали об ожогах там, где страж даже не коснулся их. Остановите Тлеющего Стража Глубин, пока он не разбудил что-то ещё в самой глубине пещер.</p>';

        $guardianEventId = LocalizedContentSeederQuery::forTable('world_events')->insertGetId([
            'title' => 'Тлеющий Страж Глубин',
            'description' => $guardianEventDescription,
            'objective_type' => 'kill',
            'image' => null,
            'map_id' => 17,
            'influence_map_id' => 17,
            'share_item_id' => null,
            'monster_id' => $guardianMonsterId,
            'influence_name' => 'Пепельные Глубины',
            'next_start_at' => $nextStartAt,
            'repeat_interval_minutes' => 240,
            'duration_minutes' => 60,
            'global_limit' => 50,
            'player_limit' => 5,
            'spawn_limit' => 5,
            'respawn_seconds' => 120,
            'item_lifetime_minutes' => 1440,
            'influence_per_item' => 1,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $guardianStageId = LocalizedContentSeederQuery::forTable('world_event_stages')->insertGetId([
            'world_event_id' => $guardianEventId,
            'position' => 1,
            'title' => 'Тлеющий Страж Глубин',
            'objective_type' => 'kill',
            'share_item_id' => null,
            'monster_id' => $guardianMonsterId,
            'global_limit' => 50,
            'player_limit' => 5,
            'spawn_limit' => 5,
            'respawn_seconds' => 120,
            'item_lifetime_minutes' => 1440,
            'influence_per_item' => 1,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('world_event_stage_targets')->insert([
            'world_event_stage_id' => $guardianStageId,
            'share_item_id' => null,
            'monster_id' => $guardianMonsterId,
            'position' => 1,
            'spawn_weight' => 100,
            'max_active' => null,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->command?->info(sprintf(
            'AshFoothillsWorldEventsSeeder: событие "Остывшие Угли Пожарища" (item id=%d, event id=%d) и "Тлеющий Страж Глубин" (monster id=%d, event id=%d)',
            $emberItemId, $emberEventId, $guardianMonsterId, $guardianEventId,
        ));
    }
}
