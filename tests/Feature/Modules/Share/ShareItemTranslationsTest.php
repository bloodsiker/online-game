<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Share;

use App\Http\Controllers\Admin\ItemController;
use App\Modules\Share\Infrastructure\Localization\LocalizedItemSeederQuery;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class ShareItemTranslationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');
    }

    public function test_same_fields_return_translations_and_fall_back_to_russian(): void
    {
        $item = new ShareItem(['name' => 'Шкатулка', 'description' => 'Описание']);
        $item->setTranslation('name', 'uk', 'Скринька');
        app()->setLocale('uk');
        $this->assertSame('Скринька', $item->name);
        $this->assertSame('Описание', $item->description);
        $this->assertSame('Шкатулка', $item->getTranslation('name', 'ru'));
        $this->assertSame(['ru' => 'Шкатулка', 'uk' => 'Скринька'], json_decode($item->getAttributes()['name'], true));
        $item->forgetTranslation('name', 'uk');
        $this->assertSame('Шкатулка', $item->name);
        $this->assertSame('', $item->getTranslation('name', 'uk', false));
    }

    public function test_plain_text_rows_remain_readable_during_rollout(): void
    {
        $item = new ShareItem;
        $item->setRawAttributes(['name' => 'Старый предмет', 'description' => null]);
        app()->setLocale('uk');
        $this->assertSame('Старый предмет', $item->name);
        $item->setTranslation('name', 'uk', 'Старий предмет');
        $this->assertSame(['ru' => 'Старый предмет', 'uk' => 'Старий предмет'], $item->getTranslations('name'));
    }

    public function test_create_and_edit_translation_panels_are_inside_the_saved_form(): void
    {
        $item = new ShareItem(['name' => 'Предмет', 'type' => 'resource']);
        $item->id = 42;
        $item->rarity = 'common';
        foreach (['stats', 'effects', 'buffs', 'debuffs', 'requirements', 'rarityUpgradeMaterials', 'itemHasItems'] as $relation) {
            $item->setRelation($relation, new Collection);
        }
        foreach (['recipe', 'useLimit', 'lockConfig', 'lockpickConfig', 'instantReward', 'magicSkillBook', 'rarityUpgradeTarget'] as $relation) {
            $item->setRelation($relation, null);
        }
        $data = ['item' => $item, 'claimedMagicSkillIds' => [], 'errors' => new ViewErrorBag];
        foreach (['skills', 'toolFamilies', 'magicSkills', 'debuffEffects', 'buffEffects', 'statTypes', 'effectTypes', 'requirementTypes', 'playerStatKeys', 'rarities', 'upgradeTargets'] as $key) {
            $data[$key] = collect();
        }
        foreach (['create', 'info'] as $page) {
            $html = view('admin.item.'.$page, $data)->render();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath = new \DOMXPath($dom);
            $formId = $page === 'create' ? 'item-create-form' : 'item-info-form';
            $this->assertSame(1, $xpath->query('//div[@id="tab-translations"]')->length, $page);
            $this->assertSame(1, $xpath->query('//form[@id="'.$formId.'"]//div[@class="tab-content"]/div[@id="tab-translations"]')->length, $page);
            $this->assertSame(2, $xpath->query('//form[@id="'.$formId.'"]//*[@name="translations[uk][name]" or @name="translations[uk][description]"]')->length, $page);
            $this->assertSame(1, $xpath->query('//a[@data-bs-target="#tab-translations" and @data-bs-toggle="tab"]')->length, $page);
            $this->assertSame(0, $xpath->query('//div[@id="tab-translations" and contains(concat(" ", @class, " "), " active ")]')->length, $page);
            $this->assertSame(1, $xpath->query('//form[@id="'.$formId.'"]/div[@class="tab-content"]/div[contains(concat(" ", @class, " "), " active ")]')->length, $page);
            foreach (['name', 'description', 'translations[uk][name]', 'translations[uk][description]'] as $field) {
                $this->assertSame(1, $xpath->query('//div[@id="tab-translations"]//*[@name="'.$field.'"]')->length, $page.': '.$field);
                $this->assertSame(0, $xpath->query('//div[@id="tab-main"]//*[@name="'.$field.'"]')->length, $page.': '.$field);
            }
            $this->assertSame(1, $xpath->query('//div[@id="item-language-ru" and contains(concat(" ", @class, " "), " active ")]')->length, $page);
            $this->assertSame(0, $xpath->query('//div[@id="item-language-uk" and contains(concat(" ", @class, " "), " active ")]')->length, $page);
            $this->assertSame(2, $xpath->query('//div[@id="tab-translations"]//button[@data-bs-toggle="tab" and @type="button"]')->length, $page);
        }
    }

    public function test_admin_edits_russian_and_ukrainian_independently_of_current_locale(): void
    {
        app()->setLocale('uk');
        $item = new ShareItem(['name' => 'До изменения', 'type' => 'resource']);
        $request = Request::create('/admin/item/1', 'POST', [
            'name' => 'Русский текст', 'type' => 'resource', 'description' => 'Описание',
            'translations' => ['uk' => ['name' => 'Український текст', 'description' => 'Опис']],
        ]);
        $method = new \ReflectionMethod(ItemController::class, 'fillItem');
        $method->invoke(app(ItemController::class), $item, $request);
        $this->assertSame('Русский текст', $item->getTranslation('name', 'ru'));
        $this->assertSame('Український текст', $item->name);
        $this->assertSame('Опис', $item->description);
        $request->merge(['translations' => ['uk' => ['name' => '', 'description' => '']]]);
        $method->invoke(app(ItemController::class), $item, $request);
        $this->assertSame('Русский текст', $item->name);
        $this->assertSame('Описание', $item->description);
    }

    public function test_migration_copies_verifies_and_swaps_columns_without_losing_ids_or_text(): void
    {
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
        });
        DB::table('share_items')->insert([
            ['id' => 42, 'name' => 'Меч «Воин»', 'description' => '<p>Первая строка</p>'."\n".'Вторая строка'],
            ['id' => 55, 'name' => 'Пустое описание', 'description' => null],
        ]);
        $migration = require database_path('migrations/2026_10_11_140000_localize_share_item_content.php');
        $migration->up();
        $migration->up();
        $row = DB::table('share_items')->where('id', 42)->first();
        $this->assertSame(['ru' => $row->name_legacy], json_decode($row->name, true));
        $this->assertSame(['ru' => $row->description_legacy], json_decode($row->description, true));
        $this->assertNull(DB::table('share_items')->where('id', 55)->value('description'));
        $this->assertSame(2, DB::table('share_items')->count());
        $this->assertFalse(Schema::hasColumn('share_items', 'name_localized'));
        $this->assertSame(42, DB::table('share_items')->where('name->ru', 'Меч «Воин»')->value('id'));
        DB::table('share_items')->insert(['id' => 60, 'name' => json_encode(['ru' => 'Новый'])]);
        $this->assertSame(3, DB::table('share_items')->count());
    }

    public function test_legacy_seeder_queries_encode_json_and_preserve_other_languages(): void
    {
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
        });
        $queryClass = LocalizedItemSeederQuery::class;
        $id = $queryClass::items()->insertGetId(['name' => 'Предмет', 'description' => '<b>Описание</b>']);
        $this->assertSame($id, $queryClass::items()->where('name', 'Предмет')->value('id'));
        DB::table('share_items')->where('id', $id)->update(['name' => json_encode(['ru' => 'Предмет', 'uk' => 'Предмет українською'])]);
        $queryClass::items()->where('id', $id)->update(['name' => "Новый 'предмет'"]);
        $translations = json_decode(DB::table('share_items')->where('id', $id)->value('name'), true);
        $this->assertSame("Новый 'предмет'", $translations['ru']);
        $this->assertSame('Предмет українською', $translations['uk']);
        $existing = $queryClass::firstOrCreate(['name' => "Новый 'предмет'"]);
        $this->assertSame($id, $existing->id);
        $this->assertSame(1, DB::table('share_items')->count());
    }
}
