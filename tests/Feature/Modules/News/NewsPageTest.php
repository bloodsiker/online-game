<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\News;

use App\Modules\Battle\Domain\Contracts\HomepageBossBattlesReader;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Forum\Application\UseCases\GetHotForumTopics;
use App\Modules\Library\Presentation\Services\LibraryShortcodeRenderer;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\News\Infrastructure\Persistence\Models\NewsPost;
use Database\Seeders\MoreGameUpdatesNewsSeeder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class NewsPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Только SQLite :memory:, игровая база не используется.');
        }
        $this->withoutVite();
        $this->mock(HomepageBossBattlesReader::class)->shouldReceive('latest')->with(10)->andReturn(new EloquentCollection);
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('last_online_at')->nullable();
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('lvl');
            $table->integer('exp');
        });
        Schema::create('clans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('lvl');
            $table->integer('experience');
        });
        (require database_path('migrations/2026_07_17_000001_create_news_posts_table.php'))->up();
        (require database_path('migrations/2026_09_16_000001_create_forum_tables.php'))->up();
        NewsPost::query()->create(['title' => 'SelectedHeadline', 'description' => '<p>SelectedBody</p>', 'is_active' => true]);
        NewsPost::query()->create(['title' => 'OtherHeadline', 'description' => '<p>OtherBody</p>', 'is_active' => true]);
        DB::table('news_comments')->insert([
            ['news_post_id' => 1, 'message' => 'VisibleComment', 'is_visible' => true, 'created_at' => now()],
            ['news_post_id' => 1, 'message' => 'HiddenComment', 'is_visible' => false, 'created_at' => now()],
            ['news_post_id' => 2, 'message' => 'OtherComment', 'is_visible' => true, 'created_at' => now()],
        ]);
    }

    public function test_selected_news_displays_only_itself_and_its_visible_comments(): void
    {
        $this->get(route('index', ['n' => 1]))->assertOk()
            ->assertSee('SelectedHeadline')->assertSee('SelectedBody')->assertSee('VisibleComment')
            ->assertDontSee('OtherHeadline')->assertDontSee('OtherBody')->assertDontSee('OtherComment')
            ->assertDontSee('HiddenComment')->assertDontSee('Гаряча пора')->assertDontSee('Зміни за 1-15 червня')
            ->assertSee('К списку новостей');
        $this->assertEquals(1, NewsPost::findOrFail(1)->views_count);
        $this->assertEquals(0, NewsPost::findOrFail(2)->views_count);
    }

    public function test_homepage_shows_five_latest_active_news_and_links_to_archive(): void
    {
        $this->archiveFixtures();
        $response = $this->get(route('index'))->assertOk()->assertSee(route('news.index'), false)
            ->assertSee('Все новости')->assertSee('ArchiveBody3')->assertDontSee('ArchiveBody19')
            ->assertDontSee('InactiveHeadline')->assertDontSee('SelectedHeadline');
        $this->assertSame([3, 23, 22, 21, 20], $response->viewData('newsPosts')->modelKeys());
        $this->assertFalse($response->viewData('isNewsArchive'));
        $this->assertSame(0, (int) NewsPost::sum('views_count'));
    }

    public function test_news_archive_paginates_ten_active_headlines_with_dates_without_comment_counts(): void
    {
        $this->archiveFixtures();
        $first = $this->get(route('news.index'))->assertOk()->assertSee('Все новости')->assertSee('ArchiveHeadline23')
            ->assertDontSee('ArchiveBody23')->assertDontSee('InactiveHeadline')->assertSee('b-pager__item--active', false)
            ->assertSee(route('news.index', ['page' => 2]), false)->assertDontSee(route('news.index', ['page' => 4]), false)
            ->assertSee('<time datetime="'.NewsPost::findOrFail(3)->created_at->toDateString().'">'.NewsPost::findOrFail(3)->created_at->format('d.m.Y').'</time>', false);
        $posts = $first->viewData('newsPosts');
        $this->assertSame(10, $posts->perPage());
        $this->assertSame(23, $posts->total());
        $this->assertSame([3, 23, 22, 21, 20, 19, 18, 17, 16, 15], $posts->getCollection()->modelKeys());
        $this->get(route('news.index', ['page' => 2]))->assertOk()->assertViewHas('newsPosts', fn ($page) => $page->count() === 10 && $page->currentPage() === 2)
            ->assertSee('<link rel="canonical" href="'.route('news.index', ['page' => 2]).'">', false)
            ->assertSee('Новости, страница 2')->assertSee('rel="prev"', false);
        $last = $this->get(route('news.index', ['page' => 3]))->assertOk()->assertSee('SelectedHeadline')->assertDontSee('1 комментарий')
            ->assertDontSee('HiddenComment')->assertDontSee('SelectedBody');
        $this->assertSame([4, 2, 1], $last->viewData('newsPosts')->getCollection()->modelKeys());
        $this->assertSame(0, (int) NewsPost::sum('views_count'));
        foreach (['page=0', 'page=-1', 'page[]=1', 'page=100001'] as $query) {
            $this->getJson(route('news.index').'?'.$query)->assertUnprocessable();
        }
    }

    public function test_archive_language_switch_preserves_page_and_translates_headlines(): void
    {
        NewsPost::findOrFail(1)->setTranslation('title', 'uk', 'УкраїнськаНовина')->save();
        $this->withSession(['language' => 'uk'])->get(route('news.index'))->assertOk()->assertSee('Усі новини')->assertSee('УкраїнськаНовина')
            ->assertDontSee('1 коментар')->assertSee('name="archive_page" value="1"', false);
        $this->withSession(['_token' => 'locale-csrf'])->withHeaders(['X-CSRF-TOKEN' => 'locale-csrf'])
            ->post(route('homepage.language'), ['language' => 'uk', 'archive_page' => 2])
            ->assertRedirect(route('news.index', ['page' => 2]))->assertSessionHas('language', 'uk');
        $this->post(route('homepage.language'), ['language' => 'ru', 'archive_page' => 1])->assertRedirect(route('news.index'));
    }

    public function test_homepage_has_floating_telegram_link_to_configured_channel(): void
    {
        config(['game.telegram_channel' => 'https://t.me/eternum_rpg']);
        $html = $this->get(route('index'))->assertOk()->getContent();
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $this->assertSame('https://t.me/eternum_rpg', $xpath->evaluate('string(//a[@class="homepage-telegram"]/@href)'));
        $this->assertSame('_blank', $xpath->evaluate('string(//a[@class="homepage-telegram"]/@target)'));
        $this->assertSame(1, $xpath->query('//a[@class="homepage-telegram"]/img')->length);
    }

    public function test_homepage_boss_battles_show_localized_names_icons_and_log_links_to_guests(): void
    {
        $boss = new Monster(['name' => ['ru' => 'Каменный Страж', 'uk' => 'Кам’яний Вартовий'], 'lvl' => 35, 'image' => 'monsters/boss.webp']);
        $boss->forceFill(['id' => 1]);
        $spawn = (new MonsterOnLocation)->setRelation('monster', $boss);
        $detail = (new BattleDetail)->setRelation('locationMonster', $spawn);
        $battle = (new Battle(['status' => BattleStatus::ACTIVE]))->forceFill(['id' => 42, 'created_at' => now()])
            ->setRelation('detailsWithMonsters', new EloquentCollection([$detail]));
        $this->mock(HomepageBossBattlesReader::class)->shouldReceive('latest')->with(10)->andReturn(new EloquentCollection([$battle]));
        $this->get(route('index'))->assertOk()->assertSee('Бои с боссами')->assertSee('Каменный Страж [35]')
            ->assertSee('monsters/boss.webp', false)->assertSee(route('fight.log', ['id' => 42]), false)->assertSee('Идёт бой');
        $this->withSession(['language' => 'uk'])->get(route('index'))->assertOk()->assertSee('Бої з босами')
            ->assertSee('Кам’яний Вартовий [35]')->assertSee('Триває бій');
    }

    public function test_homepage_still_lists_both_news_and_inactive_news_is_unavailable(): void
    {
        $this->get(route('index'))->assertOk()->assertSee('SelectedHeadline')->assertSee('OtherHeadline');
        NewsPost::findOrFail(1)->update(['is_active' => false]);
        $this->get(route('index', ['n' => 1]))->assertNotFound();
        $this->get(route('index', ['n' => 999]))->assertNotFound();
    }

    public function test_shared_renderer_supports_monsters_npcs_skills_and_spells_in_news(): void
    {
        foreach (['monsters', 'npcs', 'skills', 'magic_skills'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name): void {
                $table->id();
                $table->json('name');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                if ($name === 'monsters') {
                    $table->integer('lvl')->default(1);
                    $table->integer('hp')->default(100);
                    $table->integer('min_dmg')->default(1);
                    $table->integer('max_dmg')->default(2);
                }
                if ($name === 'npcs') {
                    $table->string('uuid');
                    $table->unsignedBigInteger('location_id')->nullable();
                }
            });
            DB::table($name)->insert(['id' => 1, 'name' => json_encode(['ru' => $name.'Name']), ...($name === 'npcs' ? ['uuid' => 'npc-test'] : [])]);
        }
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('chance');
            $table->integer('duration_seconds');
        });
        $html = app(LibraryShortcodeRenderer::class)->render('[[monster:1]] [[npc:1; display:inline]] [[skill:1]] [[magic_skill:1]] [[npc:999]]');
        foreach (['monstersName', 'npcsName', 'skillsName', 'magic_skillsName', '/info/m/catalog/1', '/info/npc/npc-test', '/magic-skill/info/1', '[[npc:999]]'] as $fragment) {
            $this->assertStringContainsString($fragment, $html);
        }
        $this->assertStringContainsString('data-library-info-popup', $html);
        $this->assertStringContainsString('library-entity-line--npc', $html);
    }

    public function test_homepage_and_news_use_the_selected_language(): void
    {
        $news = NewsPost::findOrFail(1);
        $news->setTranslation('title', 'uk', 'ВибранаНовина');
        $news->setTranslation('description', 'uk', '<p>УкраїнськийТекстНовини</p>');
        $news->save();
        $this->withSession(['language' => 'uk'])->get(route('index', ['n' => 1]))->assertOk()
            ->assertSee('lang="uk"', false)->assertSee('ВибранаНовина')->assertSee('УкраїнськийТекстНовини')
            ->assertSee('Бібліотека')->assertSee('До списку новин')->assertSee('Коментувати')
            ->assertSee('Ліцензійна угода')->assertSee('Уламки — не за горами.')
            ->assertDontSee('Введите пароль')->assertDontSee('SelectedHeadline')->assertDontSee('Комментировать');
        $this->withSession(['language' => 'ru'])->get(route('index'))->assertOk()
            ->assertSee('lang="ru"', false)->assertSee('SelectedHeadline')->assertSee('Библиотека')->assertSee('Введите пароль');
    }

    public function test_language_switch_preserves_selected_news_and_rejects_unknown_locales(): void
    {
        $this->withSession(['_token' => 'locale-csrf'])->withHeaders(['X-CSRF-TOKEN' => 'locale-csrf'])
            ->post(route('homepage.language'), ['language' => 'uk', 'news_id' => 1])
            ->assertRedirect(route('index', ['n' => 1]))->assertSessionHas('language', 'uk');
        $this->postJson(route('homepage.language'), ['language' => 'invalid'])->assertUnprocessable();
    }

    public function test_three_new_publications_are_bilingual_and_repeat_seeding_preserves_edits(): void
    {
        $this->seed(MoreGameUpdatesNewsSeeder::class);
        $this->assertSame(5, NewsPost::count());
        $news = NewsPost::query()->where('title->ru', 'Из рук в руки: торговля между игроками')->firstOrFail();
        $this->assertNotEmpty($news->getTranslation('title', 'uk', false));
        $this->assertNotEmpty($news->getTranslation('description', 'uk', false));
        $news->setTranslation('description', 'ru', 'Правки редактора');
        $news->save();
        $this->seed(MoreGameUpdatesNewsSeeder::class);
        $this->assertSame(5, NewsPost::count());
        $this->assertSame('Правки редактора', $news->fresh()->getTranslation('description', 'ru'));
    }

    public function test_forum_sidebar_shows_five_recent_public_topics_and_localized_reply_counts(): void
    {
        DB::table('forum_sections')->insert([
            ['id' => 1, 'name' => 'Public', 'slug' => 'public', 'is_active' => true, 'parent_id' => null],
            ['id' => 2, 'name' => 'Hidden', 'slug' => 'hidden', 'is_active' => false, 'parent_id' => null],
            ['id' => 3, 'name' => 'Hidden child', 'slug' => 'hidden-child', 'is_active' => true, 'parent_id' => 2],
        ]);
        foreach (range(1, 9) as $id) {
            DB::table('forum_topics')->insert([
                'id' => $id, 'section_id' => $id <= 7 ? 1 : $id - 6,
                'author_name' => 'Tester', 'title' => 'ForumTopic'.$id,
                'posts_count' => $id === 7 ? 22 : ($id === 6 ? 3 : 1),
                'last_post_at' => now()->subMinutes(10 - $id),
            ]);
        }
        $this->assertSame([7, 6, 5, 4, 3], app(GetHotForumTopics::class)->execute()->modelKeys());
        $this->get(route('index'))->assertOk()->assertSee('Горячее с форума')->assertSee('ForumTopic7')
            ->assertSee(route('forum.topic', 7), false)->assertSee('21 ответ')->assertSee('2 ответа')->assertSee('Нет ответов')
            ->assertDontSee('ForumTopic1')->assertDontSee('ForumTopic8')->assertDontSee('ForumTopic9')
            ->assertSee('homepage-heading--reference', false)->assertDontSee('Cufon.replace', false);
        $this->withSession(['language' => 'uk'])->get(route('index'))->assertOk()
            ->assertSee('Гаряче з форуму')->assertSee('21 відповідь')->assertSee('2 відповіді');
    }

    private function archiveFixtures(): void
    {
        NewsPost::query()->update(['created_at' => now()->subDays(2)]);
        foreach (range(3, 23) as $id) {
            NewsPost::query()->create(['id' => $id, 'title' => 'ArchiveHeadline'.$id, 'description' => 'ArchiveBody'.$id,
                'is_active' => true, 'created_at' => $id === 3 ? now() : now()->subDay()]);
        }
        NewsPost::query()->create(['title' => 'InactiveHeadline', 'description' => 'HiddenBody', 'is_active' => false]);
    }
}
