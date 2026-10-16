<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Share;

use App\Http\Controllers\Admin\ReputationController;
use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatMessageTranslator;
use App\Modules\Influence\Infrastructure\Persistence\Models\InfluenceMedal;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationTier;
use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ReputationMedalTranslationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');
        foreach (['reputations', 'influence_medals'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
        Schema::create('reputation_tiers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('reputation_id');
            $table->unsignedInteger('min_points')->default(0);
            $table->unsignedInteger('max_points')->nullable();
            $table->string('medal_name')->nullable();
            $table->string('medal_icon')->nullable();
            $table->text('feat_description')->nullable();
            $table->string('feat_medal_name')->nullable();
            $table->string('feat_medal_icon')->nullable();
            $table->unsignedBigInteger('feat_quest_id')->nullable();
            $table->unsignedInteger('favor_percent')->nullable();
            $table->unsignedInteger('feat_favor_percent')->nullable();
            $table->unsignedInteger('favor_duration_seconds')->nullable();
            $table->timestamps();
        });
        app('redirect')->setSession(app('session.store'));
    }

    public function test_migration_preserves_nullable_medals_ids_and_existing_locales(): void
    {
        DB::table('reputations')->insert(['id' => 10, 'name' => 'Бог Войны', 'description' => '<p>Описание</p>']);
        DB::table('influence_medals')->insert(['id' => 20, 'name' => 'Друг города']);
        DB::table('reputation_tiers')->insert([
            ['id' => 1, 'reputation_id' => 10, 'medal_name' => null, 'feat_description' => null, 'feat_medal_name' => null],
            ['id' => 2, 'reputation_id' => 10, 'medal_name' => json_encode(['ru' => 'Воин', 'uk' => 'Воїн']), 'feat_description' => 'Убить 50 монстров', 'feat_medal_name' => 'Герой'],
        ]);
        $migration = require database_path('migrations/2026_10_12_180000_localize_reputation_and_medal_content.php');
        $migration->up();
        $migration->up();
        $this->assertSame(2, ReputationTier::count());
        $this->assertFalse(ReputationTier::findOrFail(1)->hasRegularMedal());
        $this->assertFalse(ReputationTier::findOrFail(1)->hasFeatMedal());
        $this->assertNull(DB::table('reputation_tiers')->where('id', 1)->value('medal_name'));
        app()->setLocale('uk');
        $this->assertSame('Воїн', ReputationTier::findOrFail(2)->medal_name);
        $this->assertTrue(ReputationTier::findOrFail(2)->hasRegularMedal());
        $this->assertSame('Бог Войны', Reputation::findOrFail(10)->name);
        $this->assertSame('Друг города', InfluenceMedal::findOrFail(20)->name);
    }

    public function test_tier_editor_saves_languages_and_preserves_uk_on_legacy_updates(): void
    {
        app()->setLocale('uk');
        $reputation = Reputation::create(['name' => 'Бог Войны']);
        $controller = new ReputationController;
        $request = Request::create('/', 'POST', ['min_points' => 500, 'medal_name' => 'Воин', 'feat_medal_name' => 'Герой', 'feat_description' => 'Убить 50 монстров', 'translations' => ['uk' => ['medal_name' => 'Воїн', 'feat_medal_name' => 'Герой', 'feat_description' => 'Вбити 50 монстрів']]]);
        $controller->addTier(request: $request, reputation: $reputation);
        $tier = ReputationTier::firstOrFail();
        $this->assertSame('Воїн', $tier->medal_name);
        $this->assertSame('Воин', $tier->getTranslation('medal_name', 'ru'));
        $this->assertSame('Вбити 50 монстрів', $tier->feat_description);
        $request = Request::create('/', 'POST', ['min_points' => 500, 'medal_name' => 'Воитель', 'feat_medal_name' => 'Герой', 'feat_description' => 'Убить 50 монстров']);
        $controller->updateTier(request: $request, reputation: $reputation, tier: $tier);
        $this->assertSame('Воїн', $tier->refresh()->medal_name);
        $request->merge(['medal_name' => '', 'feat_medal_name' => '']);
        $controller->updateTier(request: $request, reputation: $reputation, tier: $tier);
        $this->assertFalse($tier->refresh()->hasRegularMedal());
        $this->assertSame('', $tier->getTranslation('medal_name', 'uk', false));
        $this->assertSame(0, $tier->featMedalRating());
    }

    public function test_chat_snapshot_uses_viewer_language_escapes_html_and_falls_back_to_ru(): void
    {
        $reputation = new Reputation(['name' => ['ru' => 'Бог Войны', 'uk' => '<b>Бог Війни</b>']]);
        $tier = new ReputationTier(['medal_name' => ['ru' => 'Воин', 'uk' => 'Воїн']]);
        $message = new LocalizedChatMessage('chat.medal', ['user_id' => 1, 'medal' => LocalizedChatMessage::contentName($tier, 'medal_name'), 'reputation' => LocalizedChatMessage::contentName($reputation)]);
        $translator = app(ChatMessageTranslator::class);
        $this->assertStringContainsString('Воин', $translator->render($message, 'ru'));
        $this->assertStringContainsString('Воїн', $translator->render($message, 'uk'));
        $this->assertStringContainsString('&lt;b&gt;Бог Війни&lt;/b&gt;', $translator->render($message, 'uk'));
        $reputation->name = 'Другое имя';
        $this->assertStringContainsString('Бог Войны', $translator->render($message, 'ru'));
        $fallback = new LocalizedChatMessage('chat.medal', ['user_id' => 1, 'medal' => ['translations' => ['ru' => 'Воин', 'uk' => '']], 'reputation' => 'Репутация']);
        $this->assertStringContainsString('Воин', $translator->render($fallback, 'uk'));
    }

    public function test_reputation_imports_reuse_canonical_names_and_keep_translations(): void
    {
        $first = LocalizedContentSeederQuery::firstOrCreateFor(Reputation::class, ['name' => 'Бог Войны']);
        $first->setTranslation('name', 'uk', 'Бог Війни')->save();
        $again = LocalizedContentSeederQuery::updateOrCreateFor(Reputation::class, ['name' => 'Бог Войны'], ['description' => 'Новое описание']);
        $this->assertSame($first->id, $again->id);
        $this->assertSame('Бог Війни', $again->getTranslation('name', 'uk'));
        $this->assertSame(1, Reputation::count());
    }

    public function test_repeated_medal_forms_have_unique_tab_ids_and_both_languages(): void
    {
        $tier = new ReputationTier(['medal_name' => ['ru' => 'Воин', 'uk' => 'Воїн']]);
        $html = Blade::render('@foreach([1, 2] as $id)<form>@include("admin.reputation.partials.medal-translations", ["prefix" => "tier-".$id, "content" => $tier])</form>@endforeach', ['tier' => $tier]);
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $ids = [];
        foreach ($xpath->query('//*[@id]') as $node) {
            $id = $node->getAttribute('id');
            $this->assertNotContains($id, $ids);
            $ids[] = $id;
        }
        foreach ($xpath->query('//form') as $form) {
            foreach (['medal_name', 'feat_description', 'feat_medal_name'] as $field) {
                $this->assertSame(1, $xpath->query('.//*[@name="'.$field.'"]', $form)->length);
                $this->assertSame(1, $xpath->query('.//*[@name="translations[uk]['.$field.']"]', $form)->length);
            }
        }
    }
}
