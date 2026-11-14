<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Подарки (модуль Gift):
 *  - player_gifts — подаренные игроку предметы с подписью, хранятся навсегда;
 *  - категория «Подарки» в «Торговой лавке» (монеты) и в «Премиуме» (бриллианты);
 *  - 5 подарков типа gift: три за монеты, два за бриллианты.
 * Картинки — временные заглушки в storage (items/gifts/<slug>.gif), замена — в админке предмета
 * (промпты иконок: docs/gift-icon-prompts.md). В БД хранится относительный путь диска 'public'.
 */
return new class extends Migration
{
    private const TRADE_SHOP_STRUCTURE_ID = 14;   // «Торговая лавка»

    private const PREMIUM_SHOP_STRUCTURE_ID = 10; // «Премиум»

    private const GIFTS = [
        [
            'slug' => 'bluebell-bouquet', 'rarity' => 'common', 'structure' => self::TRADE_SHOP_STRUCTURE_ID, 'price' => 300, 'diamond' => 0,
            'name' => ['ru' => 'Букет лесных колокольчиков', 'uk' => 'Букет лісових дзвіночків'],
            'description' => [
                'ru' => 'Скромный букет, собранный на опушке у Заставы. Колокольчики, говорят, звенят тише ветра — но их слышит тот, кому их подарили.',
                'uk' => 'Скромний букет, зібраний на узліссі біля Застави. Кажуть, дзвіночки дзвенять тихіше за вітер, але їх чує той, кому їх подарували.',
            ],
        ],
        [
            'slug' => 'honey-gingerbread', 'rarity' => 'uncommon', 'structure' => self::TRADE_SHOP_STRUCTURE_ID, 'price' => 800, 'diamond' => 0,
            'name' => ['ru' => 'Медовый пряник', 'uk' => 'Медовий пряник'],
            'description' => [
                'ru' => 'Расписной пряник с глазурью и пряностями из лавки на главной площади. Пахнет домом, праздником и долгой дорогой назад.',
                'uk' => 'Розписаний пряник із глазур’ю та прянощами з крамниці на головній площі. Пахне домом, святом і довгою дорогою назад.',
            ],
        ],
        [
            'slug' => 'firefly-lantern', 'rarity' => 'rare', 'structure' => self::TRADE_SHOP_STRUCTURE_ID, 'price' => 2500, 'diamond' => 0,
            'name' => ['ru' => 'Фонарь светлячков', 'uk' => 'Ліхтар світлячків'],
            'description' => [
                'ru' => 'Стеклянный фонарь, в котором до утра кружат светлячки с болот. Дарят тем, кому желают найти дорогу в самую тёмную ночь.',
                'uk' => 'Скляний ліхтар, у якому до ранку кружляють світлячки з боліт. Дарують тим, кому бажають знайти дорогу найтемнішої ночі.',
            ],
        ],
        [
            'slug' => 'crystal-rose', 'rarity' => 'epic', 'structure' => self::PREMIUM_SHOP_STRUCTURE_ID, 'price' => 0, 'diamond' => 10,
            'name' => ['ru' => 'Хрустальная роза', 'uk' => 'Кришталева троянда'],
            'description' => [
                'ru' => 'Роза, выточенная из горного хрусталя и не знающая увядания. В её лепестках навсегда застывает тепло того, кто её подарил.',
                'uk' => 'Троянда, виточена з гірського кришталю, що не знає в’янення. У її пелюстках назавжди застигає тепло того, хто її подарував.',
            ],
        ],
        [
            'slug' => 'star-of-eternity', 'rarity' => 'legendary', 'structure' => self::PREMIUM_SHOP_STRUCTURE_ID, 'price' => 0, 'diamond' => 25,
            'name' => ['ru' => 'Звезда Вечности', 'uk' => 'Зірка Вічності'],
            'description' => [
                'ru' => 'Осколок звезды, упавшей в ночь Раскалывания, оправленный в золотое кольцо Уробороса. Самый ценный подарок мира Вечности — знак того, что вы остаётесь в чьей-то памяти навсегда.',
                'uk' => 'Уламок зірки, що впала в ніч Розколу, оправлений у золоте кільце Уробороса. Найцінніший подарунок світу Вічності — знак того, що ви залишаєтеся в чиїйсь пам’яті назавжди.',
            ],
        ],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('player_gifts')) {
            Schema::create('player_gifts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
                $table->string('message', 150)->nullable();
                $table->boolean('is_anonymous')->default(false);
                $table->timestamps();
                $table->index(['recipient_id', 'id']);
            });
        }

        $now = now();
        $categoryId = DB::table('share_structure_categories')->where('name->ru', 'Подарки')->value('id')
            ?? DB::table('share_structure_categories')->insertGetId([
                'name_legacy' => 'Подарки',
                'name' => json_encode(['ru' => 'Подарки', 'uk' => 'Подарунки'], JSON_UNESCAPED_UNICODE),
                'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        foreach ([self::TRADE_SHOP_STRUCTURE_ID, self::PREMIUM_SHOP_STRUCTURE_ID] as $structureId) {
            if (DB::table('structures')->where('id', $structureId)->exists()
                && ! DB::table('shop_categories')->where('structure_id', $structureId)->where('share_structure_category_id', $categoryId)->exists()) {
                DB::table('shop_categories')->insert([
                    'structure_id' => $structureId, 'share_structure_category_id' => $categoryId, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        foreach (self::GIFTS as $sort => $gift) {
            $image = 'items/gifts/'.$gift['slug'].'.gif';
            if (! Storage::disk('public')->exists($image) && File::exists(public_path('data/smiles/gift.gif'))) {
                Storage::disk('public')->put($image, File::get(public_path('data/smiles/gift.gif')));
            }

            $shareItemId = DB::table('share_items')->where('type', 'gift')->where('name->ru', $gift['name']['ru'])->value('id')
                ?? DB::table('share_items')->insertGetId([
                    'type' => 'gift',
                    'name_legacy' => $gift['name']['ru'],
                    'description_legacy' => $gift['description']['ru'],
                    'name' => json_encode($gift['name'], JSON_UNESCAPED_UNICODE),
                    'description' => json_encode($gift['description'], JSON_UNESCAPED_UNICODE),
                    'image' => $image,
                    'rarity' => $gift['rarity'],
                    'is_two_hand' => 0, 'count_use' => 0, 'is_active' => 1,
                    'is_sell' => 0, 'is_auction_sellable' => 0, 'is_give' => 1, 'is_clan_warehouse_allowed' => 0,
                    'is_droppable' => 0, 'is_stackable' => 1, 'is_slot_usable' => 0, 'is_use' => 0, 'is_weight' => 1,
                    'price' => 0, 'diamond_price' => 0, 'break_crystal' => 0, 'upgrade_gold_cost' => 0,
                    'gathering_speed_bonus_percent' => 0, 'gathering_double_chance_percent' => 0,
                    'created_at' => $now, 'updated_at' => $now,
                ]);

            if (DB::table('structures')->where('id', $gift['structure'])->exists()
                && ! DB::table('shop_items')->where('structure_id', $gift['structure'])->where('share_item_id', $shareItemId)->exists()) {
                DB::table('shop_items')->insert([
                    'structure_id' => $gift['structure'], 'share_item_id' => $shareItemId, 'share_structure_category_id' => $categoryId,
                    'price' => $gift['price'], 'diamond' => $gift['diamond'], 'sort_order' => $sort,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('player_gifts');
        $ids = DB::table('share_items')->where('type', 'gift')
            ->whereIn('name->ru', array_map(fn (array $gift): string => $gift['name']['ru'], self::GIFTS))->pluck('id');
        DB::table('shop_items')->whereIn('share_item_id', $ids)->delete();
        DB::table('share_items')->whereIn('id', $ids)->delete();
        $categoryId = DB::table('share_structure_categories')->where('name->ru', 'Подарки')->value('id');
        if ($categoryId !== null) {
            DB::table('shop_categories')->where('share_structure_category_id', $categoryId)->delete();
            DB::table('share_structure_categories')->where('id', $categoryId)->delete();
        }
    }
};
