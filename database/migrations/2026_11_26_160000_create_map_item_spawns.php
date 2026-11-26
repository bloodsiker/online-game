<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const FOREST_MAP_ID = 2; // Шепчущий Лес

    public function up(): void
    {
        Schema::create('map_item_spawn_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->string('title', 120);
            $table->unsignedSmallInteger('per_tick')->default(5);
            $table->unsignedSmallInteger('interval_minutes')->default(10);
            $table->unsignedSmallInteger('map_max')->default(15);
            $table->unsignedInteger('ttl_minutes')->default(1440);
            $table->decimal('rain_multiplier', 4, 2)->default(2.00);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
        });

        Schema::create('map_item_spawn_rule_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rule_id')->constrained('map_item_spawn_rules')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedSmallInteger('weight')->default(1);
            $table->unsignedSmallInteger('min_count')->default(1);
            $table->unsignedSmallInteger('max_count')->default(1);
            $table->timestamps();
            $table->unique(['rule_id', 'share_item_id']);
        });

        $mushrooms = [
            ['key' => 'chanterelle', 'rarity' => 'common', 'weight' => 5,
                'name' => ['ru' => 'Лисички', 'uk' => 'Лисички'],
                'description' => [
                    'ru' => 'Яркие золотисто-оранжевые лисички с тонким грибным запахом. Их собирают в сырых лесных низинах, особенно после дождя. Любимая добавка поваров и сырьё для простых отваров алхимиков.',
                    'uk' => 'Яскраві золотисто-помаранчеві лисички з тонким грибним запахом. Їх збирають у сирих лісових низинах, особливо після дощу. Улюблена добавка кухарів і сировина для простих відварів алхіміків.',
                ]],
            ['key' => 'porcini', 'rarity' => 'uncommon', 'weight' => 3,
                'name' => ['ru' => 'Белый гриб', 'uk' => 'Білий гриб'],
                'description' => [
                    'ru' => 'Плотный белый гриб с тёмной бархатистой шляпкой — царь лесной добычи. Поварами ценится за вкус, алхимиками — за густой сок, из которого варят укрепляющие настои.',
                    'uk' => 'Щільний білий гриб із темним оксамитовим капелюшком — цар лісової здобичі. Кухарі цінують його за смак, алхіміки — за густий сік, із якого варять зміцнювальні настої.',
                ]],
            ['key' => 'fly_agaric', 'rarity' => 'rare', 'weight' => 1,
                'name' => ['ru' => 'Мерцающий мухомор', 'uk' => 'Мерехтливий мухомор'],
                'description' => [
                    'ru' => 'Редкий мухомор, чья алая шляпка в сумерках слабо мерцает голубоватыми искрами. В сыром виде ядовит. Алхимики берут его для редких зелий, а собиратели нередко заказывают его в квестах.',
                    'uk' => 'Рідкісний мухомор, чий яскраво-червоний капелюшок у сутінках слабо мерехтить блакитнуватими іскрами. У сирому вигляді отруйний. Алхіміки беруть його для рідкісних зілль, а збирачі нерідко замовляють його в завданнях.',
                ]],
        ];

        $now = now();
        $itemIds = [];
        foreach ($mushrooms as $m) {
            $row = [
                'type' => 'resource',
                'rarity' => $m['rarity'],
                'price' => 1,
                'is_stackable' => true,
                'is_active' => true,
                'is_sell' => true,
                'is_give' => true,
                'is_droppable' => true,
                'is_weight' => true,
                'is_auction_sellable' => true,
                'name' => json_encode($m['name'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode($m['description'], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            foreach (['name_legacy' => $m['name']['ru'], 'description_legacy' => $m['description']['ru']] as $column => $value) {
                if (Schema::hasColumn('share_items', $column)) {
                    $row[$column] = $value;
                }
            }
            $itemIds[$m['key']] = DB::table('share_items')->insertGetId($row);
        }

        if (! DB::table('maps')->where('id', self::FOREST_MAP_ID)->exists()) {
            return;
        }
        $ruleId = DB::table('map_item_spawn_rules')->insertGetId([
            'map_id' => self::FOREST_MAP_ID,
            'title' => 'Грибы Шепчущего Леса',
            'per_tick' => 5,
            'interval_minutes' => 10,
            'map_max' => 15,
            'ttl_minutes' => 1440,
            'rain_multiplier' => 2.00,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        foreach ($mushrooms as $m) {
            DB::table('map_item_spawn_rule_items')->insert([
                'rule_id' => $ruleId, 'share_item_id' => $itemIds[$m['key']], 'weight' => $m['weight'],
                'min_count' => 1, 'max_count' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $ids = DB::table('map_item_spawn_rule_items')->pluck('share_item_id')->all();
        Schema::dropIfExists('map_item_spawn_rule_items');
        Schema::dropIfExists('map_item_spawn_rules');
        if ($ids !== []) {
            DB::table('share_items')->whereIn('id', $ids)->delete();
        }
    }
};
