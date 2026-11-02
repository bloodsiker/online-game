<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Library;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Library\Presentation\Services\LibraryShortcodeRenderer;
use Database\Seeders\LibraryMagicSkillsSeeder;
use Database\Seeders\UnwrapLibraryMagicCatalogsSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MagicSkillsLibraryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');

        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('slug');
            $table->string('type');
            $table->boolean('is_passive')->default(false);
            $table->json('effects')->nullable();
            $table->string('image')->nullable();
            foreach (['mana_cost', 'cooldown', 'min_damage', 'max_damage', 'base_healing'] as $field) {
                $table->integer($field)->default(0);
            }
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->json('description')->nullable();
            $table->json('stat_modifiers')->nullable();
            $table->string('slug')->nullable();
        });
        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('duration_seconds')->default(15);
            $table->integer('chance')->default(100);
        });
        Schema::create('library_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->json('name');
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('library_articles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('slug');
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('content');
            $table->string('status');
            $table->timestamp('published_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_catalogs_separate_skill_types_and_exclude_clan_ranks(): void
    {
        foreach ([['attack', false, 'fire'], ['buff', false, 'blessing'], ['debuff', false, 'curse'], ['utility', true, 'passive'], ['buff', true, 'clan_1_lvl_1']] as $index => [$type, $passive, $slug]) {
            DB::table('magic_skills')->insert([
                'id' => $index + 1, 'name' => json_encode(['ru' => $slug, 'uk' => $slug]),
                'slug' => $slug, 'type' => $type, 'is_passive' => $passive, 'image' => 'skills/test.png',
            ]);
        }
        foreach (['combat' => [1], 'support' => [2, 3], 'passive' => [4]] as $group => $expectedIds) {
            $renderer = app(LibraryShortcodeRenderer::class);
            $html = $renderer->render('[[magic_skill_catalog:'.$group.']]');
            preg_match_all('/data-id="library_magic_(\d+)"/', $html, $matches);
            $this->assertSame($expectedIds, array_map('intval', $matches[1]));
            $this->assertStringContainsString('showItemInfo(this,event,2)', $html);
            $this->assertStringContainsString('skills/test.png', $html);
            $this->assertStringContainsString('library_magic_'.$expectedIds[0], $renderer->tooltipScript());
        }
    }

    public function test_passive_families_have_separate_blocks_with_six_rank_icons(): void
    {
        foreach (['skin' => 'Кожа', 'power' => 'Сила'] as $family => $name) {
            foreach ([1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI'] as $rank => $roman) {
                DB::table('magic_skills')->insert([
                    'name' => json_encode(['ru' => $name.' '.$roman]), 'slug' => $family.'-'.$rank,
                    'type' => 'utility', 'is_passive' => true,
                ]);
            }
        }
        $html = app(LibraryShortcodeRenderer::class)->render('[[magic_skill_catalog:passive]]');
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $blocks = $xpath->query('//table[contains(@class,"library-magic-group")]');
        $this->assertCount(2, $blocks);
        foreach ($blocks as $block) {
            $this->assertSame(6, $xpath->query('.//a[@data-id]', $block)->length);
            $this->assertSame(6, $xpath->query('.//a[@data-id]//img', $block)->length);
        }
        $this->assertStringContainsString('>VI</b>', $html);
    }

    public function test_support_catalog_separates_buffs_debuffs_and_healing(): void
    {
        foreach (['buff', 'debuff', 'heal'] as $type) {
            DB::table('magic_skills')->insert([
                'name' => json_encode(['ru' => $type]), 'slug' => $type, 'type' => $type,
            ]);
        }
        $html = app(LibraryShortcodeRenderer::class)->render('[[magic_skill_catalog:support]]');
        $this->assertSame(3, substr_count($html, 'library-magic-group'));
        $this->assertStringNotContainsString('library-entity-card', $html);
        $this->assertSame(3, substr_count($html, 'user-reward-frame.png'));
        foreach (['Баффы', 'Дебаффы', 'Исцеление'] as $heading) {
            $this->assertStringContainsString($heading.'</td>', $html);
        }
    }

    public function test_tooltips_use_actual_bonuses_and_escape_content_in_both_locales(): void
    {
        DB::table('magic_skills')->insert([
            'id' => 1, 'name' => json_encode(['ru' => 'Клич', 'uk' => 'КличУК']),
            'slug' => 'cry', 'type' => 'buff', 'description' => json_encode(['ru' => '<script>bad()</script>', 'uk' => 'Опис']),
        ]);
        DB::table('effects')->insert([
            'id' => 1, 'name' => json_encode(['ru' => 'Усиление', 'uk' => 'Посилення']),
            'description' => json_encode(['ru' => '+30% старое', 'uk' => '+30% старе']),
            'stat_modifiers' => json_encode([['type' => 'attack', 'value' => 8, 'is_percent' => true]]),
        ]);
        DB::table('magic_skill_effects')->insert(['magic_skill_id' => 1, 'effect_id' => 1]);
        foreach (['ru', 'uk'] as $locale) {
            app()->setLocale($locale);
            $renderer = app(LibraryShortcodeRenderer::class);
            $renderer->render('[[magic_skill:1]]');
            $script = $renderer->tooltipScript();
            $this->assertStringContainsString('+8%', $script);
            $this->assertStringNotContainsString('+30%', $script);
            $this->assertStringNotContainsString('<script>bad()', $script);
            $this->assertStringContainsString($locale === 'uk' ? 'Посилення' : 'Усиление', $script);
        }
    }

    public function test_unwrap_moves_catalog_outside_tables_and_preserves_other_content(): void
    {
        Storage::fake('local');
        $this->seed(LibraryMagicSkillsSeeder::class);
        $article = LibraryArticle::where('slug', 'passivnye-magicheskie-navyki')->firstOrFail();
        $html = '<p>Моя правка</p><table class="library-game-frame"><tbody><tr><td>Описание</td></tr></tbody></table>'
            .'<table class="library-game-frame"><tbody><tr><td class="tbl-usi_bg"><p>Подсказка</p>[[magic_skill_catalog:passive]]</td></tr></tbody></table><p>Конец</p>';
        $article->setTranslation('content', 'ru', $html)->save();
        $this->seed(UnwrapLibraryMagicCatalogsSeeder::class);
        $content = $article->fresh()->getTranslation('content', 'ru');
        $this->assertSame('<p>Моя правка</p><table class="library-game-frame"><tbody><tr><td>Описание</td></tr></tbody></table><p>Подсказка</p>[[magic_skill_catalog:passive]]<p>Конец</p>', $content);
        $this->seed(UnwrapLibraryMagicCatalogsSeeder::class);
        $this->assertSame($content, $article->fresh()->getTranslation('content', 'ru'));
        $this->assertCount(1, Storage::disk('local')->allFiles('backups/library'));
    }

    public function test_seeder_publishes_three_localized_articles_and_preserves_edits(): void
    {
        $this->seed(LibraryMagicSkillsSeeder::class);
        $this->assertSame(1, LibraryCategory::count());
        $this->assertSame(3, LibraryArticle::published()->count());
        foreach (LibraryArticle::all() as $article) {
            foreach (['ru', 'uk'] as $locale) {
                $this->assertStringContainsString('[[magic_skill_catalog:', $article->getTranslation('content', $locale));
                $this->assertStringContainsString('library-game-frame__title', $article->getTranslation('content', $locale));
            }
        }
        $article = LibraryArticle::firstOrFail();
        $article->setTranslation('content', 'ru', 'Моя правка')->save();
        $this->seed(LibraryMagicSkillsSeeder::class);
        $this->assertSame(3, LibraryArticle::count());
        $this->assertSame('Моя правка', $article->fresh()->getTranslation('content', 'ru'));
    }
}
