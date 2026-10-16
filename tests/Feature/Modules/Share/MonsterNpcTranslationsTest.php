<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Share;

use App\Modules\Battle\Application\Services\BattleLogTranslator;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Npc\Infrastructure\Persistence\Models\Npc;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

final class MonsterNpcTranslationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');
    }

    public function test_migration_preserves_plain_and_previously_translated_content_and_ids(): void
    {
        foreach (['monsters', 'npcs'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
            });
            DB::table($tableName)->insert([
                ['id' => 42, 'name' => 'Исходное имя', 'description' => null],
                ['id' => 55, 'name' => json_encode(['ru' => 'Волк', 'uk' => 'Вовк']), 'description' => '<p>Описание</p>'],
            ]);
        }
        $migration = require database_path('migrations/2026_10_12_140000_localize_monster_and_npc_content.php');
        $migration->up();
        $migration->up();
        foreach (['monsters', 'npcs'] as $tableName) {
            $this->assertSame(2, DB::table($tableName)->count());
            $this->assertSame(['ru' => 'Исходное имя'], json_decode(DB::table($tableName)->where('id', 42)->value('name'), true));
            $this->assertSame(['ru' => 'Волк', 'uk' => 'Вовк'], json_decode(DB::table($tableName)->where('id', 55)->value('name'), true));
            $this->assertSame(55, DB::table($tableName)->where('name->uk', 'Вовк')->value('id'));
            $this->assertNull(DB::table($tableName)->where('id', 42)->value('description'));
        }
    }

    public function test_editor_keeps_locales_separate_and_clearing_translation_enables_fallback(): void
    {
        foreach ([new Monster, new Npc] as $model) {
            app()->setLocale('uk');
            $request = Request::create('/', 'POST', ['name' => 'Волк', 'description' => '<p>Описание</p>', 'translations' => ['uk' => ['name' => 'Вовк', 'description' => '<p>Опис</p>']]]);
            app(ContentTranslationEditor::class)->fill($model, $request);
            $this->assertSame('Вовк', $model->name);
            $this->assertSame('Волк', $model->getTranslation('name', 'ru'));
            $request->merge(['translations' => ['uk' => ['name' => null, 'description' => '']]]);
            app(ContentTranslationEditor::class)->fill($model, $request);
            $this->assertSame('Волк', $model->name);
        }
    }

    public function test_search_matches_both_locales_and_does_not_bypass_other_filters(): void
    {
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
        });
        DB::table('monsters')->insert([
            ['id' => 1, 'name' => json_encode(['ru' => 'Wolf', 'uk' => 'Вовк']), 'description' => null],
            ['id' => 2, 'name' => json_encode(['ru' => 'Волк', 'uk' => 'WOLF']), 'description' => null],
        ]);
        $this->assertSame(2, Monster::query()->searchTranslations('%wolf%')->count());
        $this->assertSame(1, Monster::query()->whereKey(1)->searchTranslations('%wolf%')->count());
    }

    public function test_seeders_reuse_ids_and_preserve_ukrainian_translation(): void
    {
        foreach (['monsters', 'npcs'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->json('name');
                $table->json('description')->nullable();
                $table->boolean('is_boss')->default(false);
                $table->string('uuid')->nullable();
                $table->timestamps();
            });
        }
        foreach ([Monster::class, Npc::class] as $modelClass) {
            $modelClass::withoutEvents(function () use ($modelClass): void {
                $helper = LocalizedContentSeederQuery::class;
                $first = $helper::firstOrCreateFor($modelClass, ['name' => 'Исходное имя']);
                $first->setTranslation('name', 'uk', 'Українське ім’я')->save();
                $again = $helper::updateOrCreateFor($modelClass, ['name' => 'Исходное имя'], ['description' => 'Новое описание']);
                $this->assertSame($first->id, $again->id);
                $this->assertSame('Українське ім’я', $again->getTranslation('name', 'uk'));
                $this->assertSame(1, $modelClass::query()->count());
            });
        }
    }

    public function test_new_battle_logs_render_monster_names_for_the_viewer_without_queries(): void
    {
        $monster = new Monster(['name' => ['ru' => 'Глиняный голем', 'uk' => 'Глиняний голем']]);
        app()->setLocale('uk');
        DB::enableQueryLog();
        $stored = BattleLogTranslator::format('<p>%s прыгнул на вас. <br>Повреждения: <b>%s</b></p>', BattleLogTranslator::contentName($monster), 42);
        $this->assertStringContainsString('Глиняный голем', $stored);
        $this->assertStringContainsString('Глиняний голем', BattleLogTranslator::render($stored));
        app()->setLocale('ru');
        $this->assertStringContainsString('Глиняный голем', BattleLogTranslator::render($stored));
        $this->assertSame([], DB::getQueryLog());
    }

    public function test_all_four_admin_forms_submit_both_languages_inside_the_main_form(): void
    {
        $monster = new Monster(['name' => 'Волк']);
        $monster->id = 42;
        foreach (['items', 'locations', 'effects', 'phases', 'mechanics', 'summonPool'] as $relation) {
            $monster->setRelation($relation, new Collection);
        }
        $npc = new Npc(['name' => 'Торговец']);
        $npc->id = 55;
        $npc->setRelation('location', null);
        foreach (['dialogueNodes', 'startQuests', 'completeQuests'] as $relation) {
            $npc->setRelation($relation, new Collection);
        }
        $data = ['monster' => $monster, 'npc' => $npc, 'errors' => new ViewErrorBag];
        foreach (['allMonsters', 'availableEffects', 'mechanicTypes', 'statTypes', 'effectTypes'] as $key) {
            $data[$key] = collect();
        }
        foreach (['monster', 'npc'] as $type) {
            foreach (['create', 'info'] as $page) {
                $html = view('admin.'.$type.'.'.$page, $data)->render();
                $document = new \DOMDocument;
                @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
                $xpath = new \DOMXPath($document);
                $form = $xpath->query('//form[@enctype="multipart/form-data"]')->item(0);
                $this->assertNotNull($form);
                foreach (['name', 'description', 'translations[uk][name]', 'translations[uk][description]'] as $name) {
                    $fields = $type === 'monster' && $page === 'info'
                        ? $xpath->query('//*[@form="monster-info-form" and @name="'.$name.'"]')
                        : $xpath->query('.//*[@name="'.$name.'"]', $form);
                    $this->assertSame(1, $fields->length, $type.' '.$page.' '.$name);
                }
                $panel = $xpath->query('//div[@id="tab-translations"]')->item(0);
                $this->assertNotNull($panel);
                $this->assertSame('tab-content', $panel->parentNode->getAttribute('class'));
            }
        }
    }
}
