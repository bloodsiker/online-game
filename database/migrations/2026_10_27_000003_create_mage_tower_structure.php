<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Башня магов — структура на локации 43 («Торговая улица»), покупка
 * гримуаров заклинаний (см. 2026_10_26_000001, 2026_10_27_000001) за золото,
 * buy-only поток (см. MageTowerController, тот же паттерн что у
 * BarterShopController). Сам «Изучить» из рюкзака дополнительно требует
 * нахождения в этой структуре — см. правку LearnMagicSkillFromBook.
 */
return new class extends Migration
{
    private const TYPES = [
        'shop', 'auction', 'heal', 'warehouse', 'clan_warehouse', 'bank', 'clan_bank',
        'blacksmith', 'exchange', 'auction_exchange', 'clan_skill_hall', 'reputation_exchange',
        'barter_shop', 'workshop', 'tool_workshop', 'clan_boss', 'prison', 'mage_tower',
    ];

    /** @var array<string, string> тип скилла → название категории в башне */
    private const CATEGORY_BY_SKILL_TYPE = [
        'attack' => 'Гримуары (атака)',
        'debuff' => 'Гримуары (дебафф)',
        'buff' => 'Гримуары (поддержка)',
        'heal' => 'Гримуары (поддержка)',
        'utility' => 'Гримуары (пассивка)',
    ];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $types = "'".implode("','", self::TYPES)."'";
            DB::statement("ALTER TABLE structures MODIFY type ENUM({$types}) NULL DEFAULT 'shop'");
        }

        $now = now();

        $structureId = DB::table('structures')->where('type', 'mage_tower')->where('location_id', 43)->value('id');
        if ($structureId === null) {
            $structureId = DB::table('structures')->insertGetId([
                'type' => 'mage_tower',
                'location_id' => 43,
                'name' => json_encode(['ru' => 'Башня магов', 'uk' => 'Башта магів'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode([
                    'ru' => 'Здесь продаются гримуары заклинаний и можно изучить их силу.',
                    'uk' => 'Тут продаються гримуари заклинань і можна вивчити їхню силу.',
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $categoryIdByName = [];
        foreach (array_unique(self::CATEGORY_BY_SKILL_TYPE) as $categoryName) {
            $shareCategoryId = DB::table('share_structure_categories')->where('name->ru', $categoryName)->value('id');
            if ($shareCategoryId === null) {
                $shareCategoryId = DB::table('share_structure_categories')->insertGetId([
                    'name' => json_encode(['ru' => $categoryName, 'uk' => $categoryName], JSON_UNESCAPED_UNICODE),
                    'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $shopCategoryExists = DB::table('shop_categories')
                ->where('structure_id', $structureId)->where('share_structure_category_id', $shareCategoryId)->exists();
            if (! $shopCategoryExists) {
                DB::table('shop_categories')->insert([
                    'structure_id' => $structureId, 'share_structure_category_id' => $shareCategoryId,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $categoryIdByName[$categoryName] = $shareCategoryId;
        }

        $books = DB::table('magic_skill_books')
            ->join('magic_skills', 'magic_skills.id', '=', 'magic_skill_books.magic_skill_id')
            ->join('share_items', 'share_items.id', '=', 'magic_skill_books.share_item_id')
            ->select('share_items.id as share_item_id', 'share_items.price', 'magic_skills.type', 'magic_skills.is_passive')
            ->get();

        $sortOrder = 0;
        foreach ($books as $book) {
            $skillType = $book->is_passive ? 'utility' : $book->type;
            $categoryName = self::CATEGORY_BY_SKILL_TYPE[$skillType] ?? 'Гримуары (поддержка)';
            $shareCategoryId = $categoryIdByName[$categoryName];

            $exists = DB::table('shop_items')
                ->where('structure_id', $structureId)->where('share_item_id', $book->share_item_id)->exists();
            if (! $exists) {
                DB::table('shop_items')->insert([
                    'structure_id' => $structureId, 'share_item_id' => $book->share_item_id,
                    'share_structure_category_id' => $shareCategoryId, 'price' => $book->price,
                    'diamond' => 0, 'sort_order' => $sortOrder, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            $sortOrder++;
        }
    }

    public function down(): void
    {
        $structureId = DB::table('structures')->where('type', 'mage_tower')->where('location_id', 43)->value('id');

        if ($structureId !== null) {
            DB::table('shop_items')->where('structure_id', $structureId)->delete();
            DB::table('shop_categories')->where('structure_id', $structureId)->delete();
            DB::table('structures')->where('id', $structureId)->delete();
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::table('structures')->where('type', 'mage_tower')->update(['type' => 'shop']);
            $types = "'shop','auction','heal','warehouse','clan_warehouse','bank','clan_bank','blacksmith','exchange','auction_exchange','clan_skill_hall','reputation_exchange','barter_shop','workshop','tool_workshop','clan_boss','prison'";
            DB::statement("ALTER TABLE structures MODIFY type ENUM({$types}) NULL DEFAULT 'shop'");
        }
    }
};
