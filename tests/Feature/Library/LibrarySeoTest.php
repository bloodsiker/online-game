<?php

declare(strict_types=1);

namespace Tests\Feature\Library;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Library\Presentation\Services\LibrarySeo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class LibrarySeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // This suite owns only an in-memory SQLite connection, never the game's database.
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('last_online_at')->nullable();
        });
        $migration = require database_path('migrations/2026_09_10_000005_create_library_tables.php');
        $migration->up();
        Schema::table('library_categories', function (Blueprint $table): void {
            $table->string('content_type')->default('articles');
        });
    }

    public function test_article_metadata_and_body_use_url_language_and_clean_canonical(): void
    {
        $article = $this->article();
        $article->forceFill(['updated_at' => now()->subDays(2)])->save();
        $modifiedAt = $article->updated_at->toAtomString();

        $response = $this->withSession(['language' => 'ru'])
            ->get('/uk/library/treasure?category=guide&utm_source=test')
            ->assertOk()->assertHeader('Content-Language', 'uk')
            ->assertSee('<html lang="uk">', false)
            ->assertSee('<title>Пошук скарбів — Бібліотека — Вічність</title>', false)
            ->assertSee('Український текст')
            ->assertSee('<h1 class="library-page-title">Пошук скарбів</h1>', false);
        $xpath = $this->xpath($response->getContent());
        $this->assertSame(url('/uk/library/treasure'), $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame('Як знайти скарб & отримати нагороду.', $xpath->evaluate('string(//meta[@name="description"]/@content)'));
        $this->assertSame(url('/ru/library/treasure'), $xpath->evaluate('string(//link[@hreflang="ru"]/@href)'));
        $this->assertSame(url('/uk/library/treasure'), $xpath->evaluate('string(//link[@hreflang="uk"]/@href)'));
        $this->assertSame('article', $xpath->evaluate('string(//meta[@property="og:type"]/@content)'));
        $this->assertSame('uk_UA', $xpath->evaluate('string(//meta[@property="og:locale"]/@content)'));
        $this->assertSame(url('/uk/library/treasure'), $xpath->evaluate('string(//meta[@property="og:url"]/@content)'));
        $this->assertSame(asset('covers/treasure.jpg'), $xpath->evaluate('string(//meta[@property="og:image"]/@content)'));
        $this->assertSame($modifiedAt, $xpath->evaluate('string(//meta[@property="article:modified_time"]/@content)'));
        $this->assertSame($modifiedAt, $article->fresh()->updated_at->toAtomString());
        $this->assertSame(1, $article->fresh()->views_count);

        $this->withSession(['language' => 'uk'])->get('/ru/library/treasure')
            ->assertOk()->assertHeader('Content-Language', 'ru')
            ->assertSee('<html lang="ru">', false)->assertSee('Русский текст');
        $this->assertSame('ru', app()->getLocale());
    }

    public function test_legacy_links_redirect_with_filters_preserved_and_without_changing_views(): void
    {
        $article = $this->article();
        $this->withSession(['language' => 'uk'])->get('/library/treasure?category=guide')
            ->assertRedirect(url('/uk/library/treasure').'?category=guide');
        $this->get('/library?category=guide&q=treasure&page=2')
            ->assertRedirect(url('/uk/library').'?category=guide&q=treasure&page=2');
        $this->assertSame(0, $article->fresh()->views_count);
    }

    public function test_home_has_language_links_metadata_and_language_specific_navigation(): void
    {
        $this->article();
        $response = $this->get('/uk/library')->assertOk()
            ->assertSee('Бібліотека гри', false)
            ->assertSee('href="'.url('/uk/library').'?category=guide"', false)
            ->assertSee('href="'.url('/ru/library').'" lang="ru" hreflang="ru"', false);
        $xpath = $this->xpath($response->getContent());
        $this->assertSame('website', $xpath->evaluate('string(//meta[@property="og:type"]/@content)'));
        $this->assertSame(url('/uk/library'), $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame(1, $xpath->query('//h1')->length);
        $this->get('/uk/library?category=guide')
            ->assertRedirect(url('/uk/library/treasure').'?category=guide');
        $this->get('/en/library')->assertNotFound();
    }

    public function test_shared_layout_still_renders_forum_pages_without_library_metadata(): void
    {
        $html = Blade::render('@extends("library::layout")
            @section("title", "Тест форума")
            @section("site-section", "Форум")
            @section("sidebar", "Разделы форума")
            @section("content", "Контент форума")');

        $this->assertStringContainsString('<title>Тест форума — Форум</title>', $html);
        $this->assertStringContainsString('Контент форума', $html);
        $this->assertStringNotContainsString('class="library-language-switch"', $html);
        $this->assertStringNotContainsString('<h1 class="library-page-title">', $html);
    }

    public function test_article_description_falls_back_to_clean_text_and_then_localized_summary(): void
    {
        $article = $this->article();
        $article->setTranslation('excerpt', 'ru', '');
        $article->setTranslation('content', 'ru', '<p>Первый &amp; второй.</p><p>Третий.</p>[[placeholder:123]]<script>bad()</script>');
        $article->save();
        $response = $this->get('/ru/library/treasure')->assertOk();
        $xpath = $this->xpath($response->getContent());
        $this->assertSame('Первый & второй. Третий.', $xpath->evaluate('string(//meta[@name="description"]/@content)'));

        $article->setTranslation('content', 'ru', '[[artifact_catalog]]')->save();
        $seo = app(LibrarySeo::class)->article($article);
        $this->assertSame('Поиск сокровищ — руководство в библиотеке игры «Вечность».', $seo->description);
    }

    public function test_internal_editorial_links_keep_the_selected_language_and_external_links_stay_external(): void
    {
        $article = $this->article();
        $article->setTranslation('content', 'uk', '<a href="/library/other?category=guide&amp;q=x#part">Стаття</a>'
            .'<a href="'.url('/library').'?category=guide">Розділ</a>'
            .'<a href="https://example.org/library/other">External</a>')->save();

        $this->get('/uk/library/treasure')->assertOk()
            ->assertSee('href="'.url('/uk/library/other').'?category=guide&amp;q=x#part"', false)
            ->assertSee('href="'.url('/uk/library').'?category=guide"', false)
            ->assertSee('href="https://example.org/library/other"', false);
    }

    public function test_search_is_noindex_and_pagination_is_self_canonical_without_tracking_parameters(): void
    {
        $this->article();
        $response = $this->get('/uk/library?q=скарб&page=2&utm_source=test')->assertOk();
        $xpath = $this->xpath($response->getContent());
        $this->assertSame('noindex, follow', $xpath->evaluate('string(//meta[@name="robots"]/@content)'));
        $this->assertSame(url('/uk/library').'?q='.urlencode('скарб').'&page=2', $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertStringContainsString('Сторінка 2', $xpath->evaluate('string(//title)'));

        $response = $this->get('/ru/library?page=2&utm_source=test')->assertOk();
        $xpath = $this->xpath($response->getContent());
        $this->assertSame(url('/ru/library'), $xpath->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame('index, follow', $xpath->evaluate('string(//meta[@name="robots"]/@content)'));

        $category = new LibraryCategory(['name' => 'Бестиарий', 'slug' => 'monsters', 'content_type' => 'monsters']);
        $seo = app(LibrarySeo::class)->index(
            request: Request::create('/ru/library?category=monsters&page=2&utm_source=test'),
            category: $category,
        );
        $this->assertSame(url('/ru/library').'?category=monsters&page=2', $seo->canonical);
        $this->assertSame('index, follow', $seo->robots);
    }

    public function test_missing_translation_redirects_to_existing_language_and_is_not_advertised(): void
    {
        $article = $this->article();
        $article->forgetTranslation('content', 'uk')->save();
        $this->get('/uk/library/treasure')->assertRedirect(url('/ru/library/treasure'));
        $response = $this->get('/ru/library/treasure')->assertOk();
        $xpath = $this->xpath($response->getContent());
        $this->assertSame(0, $xpath->query('//link[@hreflang="uk"]')->length);
        $this->assertSame(0, $xpath->query('//meta[@property="og:locale:alternate"]')->length);
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/uk/library/treasure', false);
    }

    public function test_sitemap_contains_only_available_published_articles_and_actual_catalog_pages(): void
    {
        $article = $this->article();
        $this->article(slug: 'draft', status: LibraryArticle::STATUS_DRAFT);
        $this->article(slug: 'future', publishedAt: now()->addDay()->toDateTimeString());
        $deleted = $this->article(slug: 'deleted');
        $deleted->delete();
        $hidden = $this->article(slug: 'hidden');
        $hidden->category->update(['is_active' => false]);
        LibraryCategory::query()->create(['name' => 'Монстры', 'slug' => 'monsters', 'content_type' => 'monsters', 'is_active' => true]);
        LibraryCategory::query()->create(['name' => 'Карта', 'slug' => 'maps', 'content_type' => 'maps', 'is_active' => true]);

        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $locations = array_map('strval', $xml->xpath('//s:loc'));
        $this->assertCount(8, $locations);
        foreach (['ru', 'uk'] as $locale) {
            $this->assertContains(url('/'.$locale.'/library'), $locations);
            $this->assertContains(url('/'.$locale.'/library/treasure'), $locations);
            $this->assertContains(url('/'.$locale.'/library').'?category=monsters', $locations);
            $this->assertContains(url('/'.$locale.'/library').'?category=maps', $locations);
        }
        foreach (['draft', 'future', 'hidden', 'deleted'] as $slug) {
            $response->assertDontSee('/library/'.$slug, false);
            $this->get('/ru/library/'.$slug)->assertNotFound();
        }
        $this->assertSame(0, $article->fresh()->views_count);
        $this->assertCount(0, $response->headers->getCookies());
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: '.url('/sitemap.xml'), false);
    }

    private function article(string $slug = 'treasure', string $status = LibraryArticle::STATUS_PUBLISHED, ?string $publishedAt = null): LibraryArticle
    {
        $category = LibraryCategory::query()->create([
            'name' => ['ru' => 'Руководства', 'uk' => 'Посібники'],
            'slug' => $slug === 'treasure' ? 'guide' : 'guide-'.$slug,
            'is_active' => true,
        ]);

        return LibraryArticle::query()->create([
            'category_id' => $category->id,
            'slug' => $slug,
            'title' => ['ru' => 'Поиск сокровищ', 'uk' => 'Пошук скарбів'],
            'excerpt' => ['ru' => 'Как найти клад.', 'uk' => 'Як знайти скарб & отримати нагороду.'],
            'content' => ['ru' => '<p>Русский текст</p>', 'uk' => '<p>Український текст</p>'],
            'cover_image' => 'covers/treasure.jpg',
            'status' => $status,
            'published_at' => $publishedAt ?? now()->subHour(),
        ]);
    }

    private function xpath(string $html): \DOMXPath
    {
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);

        return new \DOMXPath($document);
    }
}
