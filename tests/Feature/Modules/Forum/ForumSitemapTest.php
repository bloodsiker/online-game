<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Forum;

use App\Modules\Forum\Infrastructure\Persistence\Models\ForumPost;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumSection;
use App\Modules\Forum\Infrastructure\Persistence\Models\ForumTopic;
use App\Modules\Forum\Presentation\Services\ForumSitemap;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class ForumSitemapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'session.driver' => 'array', 'cache.default' => 'array']);
        DB::purge('sqlite');
        app()->setLocale('ru');
        $this->travelTo(now()->startOfSecond());
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('last_online_at')->nullable();
        });
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
        });
        (require database_path('migrations/2026_09_10_000005_create_library_tables.php'))->up();
        Schema::table('library_categories', function (Blueprint $table): void {
            $table->string('content_type')->default('articles');
        });
        (require database_path('migrations/2026_09_16_000001_create_forum_tables.php'))->up();
        (require database_path('migrations/2026_09_18_000004_add_posting_permissions_to_forum_sections.php'))->up();
    }

    public function test_sitemap_is_public_and_excludes_inactive_branches_parent_headings_and_deleted_topics(): void
    {
        $parent = $this->section('root');
        $section = $this->section('discussion', parent: $parent);
        $closedSection = $this->section('announcements');
        $closedSection->update(['allow_topics' => false, 'allow_comments' => false]);
        $hidden = $this->section('hidden', active: false);
        $hiddenChild = $this->section('hidden-child', parent: $hidden);
        $topic = $this->topic($section);
        $lockedTopic = $this->topic($closedSection);
        $lockedTopic->update(['is_locked' => true]);
        $hiddenTopic = $this->topic($hiddenChild);
        $deleted = $this->topic($section);
        $deletedUrl = route('forum.topic', $deleted->id);
        $deleted->delete();

        $response = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $urls = $this->locations($response->getContent());
        foreach ([route('forum.index'), route('forum.section', 'discussion'), route('forum.section', 'announcements'),
            route('forum.topic', $topic->id), route('forum.topic', $lockedTopic->id)] as $url) {
            $this->assertContains($url, $urls);
        }
        foreach ([route('forum.section', 'root'), route('forum.section', 'hidden'), route('forum.section', 'hidden-child'),
            route('forum.topic', $hiddenTopic->id), $deletedUrl] as $url) {
            $this->assertNotContains($url, $urls);
        }
        $response->assertDontSee('/forum/search', false)->assertDontSee('/edit', false)->assertDontSee('#post-', false);
        $this->assertSame(0, $topic->fresh()->views_count);
        $this->assertCount(0, $response->headers->getCookies());
    }

    public function test_pagination_uses_actual_topic_and_post_counts_and_lastmod_includes_post_edits(): void
    {
        $section = $this->section('discussion');
        $first = $this->topic($section);
        foreach (range(1, 20) as $number) {
            $this->topic($section);
        }
        foreach (range(1, 16) as $number) {
            $this->createPost($first);
        }
        $editedAt = now()->subHour();
        ForumPost::query()->where('topic_id', $first->id)->firstOrFail()->forceFill(['updated_at' => $editedAt])->save();

        $response = $this->get('/sitemap.xml')->assertOk();
        $urls = $this->locations($response->getContent());
        $this->assertContains(route('forum.section', ['slug' => 'discussion', 'page' => 2]), $urls);
        $this->assertNotContains(route('forum.section', ['slug' => 'discussion', 'page' => 3]), $urls);
        $this->assertContains(route('forum.topic', ['id' => $first->id, 'page' => 2]), $urls);
        $this->assertNotContains(route('forum.topic', ['id' => $first->id, 'page' => 3]), $urls);
        $this->assertCount(count(array_unique($urls)), $urls);
        $this->assertSame(0, $first->fresh()->posts_count, 'Fixture intentionally has a stale denormalized counter.');

        $xml = simplexml_load_string($response->getContent());
        $lastmod = null;
        foreach ($xml->url as $entry) {
            if ((string) $entry->loc === route('forum.topic', $first->id)) {
                $lastmod = (string) $entry->lastmod;
            }
        }
        $this->assertSame($editedAt->toAtomString(), $lastmod);
    }

    public function test_new_topics_and_section_visibility_are_reflected_on_next_request_without_manual_generation(): void
    {
        $section = $this->section('discussion');
        $before = $this->get('/sitemap.xml')->assertOk();
        $topic = $this->topic($section);
        $this->assertNotContains(route('forum.topic', $topic->id), $this->locations($before->getContent()));
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('forum.topic', $topic->id), false);
        $section->update(['is_active' => false]);
        $this->get('/sitemap.xml')->assertOk()->assertDontSee(route('forum.topic', $topic->id), false);
    }

    public function test_forum_generation_batches_queries_instead_of_querying_each_topic(): void
    {
        $section = $this->section('discussion');
        foreach (range(1, 30) as $number) {
            $this->createPost($this->topic($section));
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $entries = app(ForumSitemap::class)->entries();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(3, count($queries));
        $this->assertCount(33, $entries); // Index, two section pages, thirty topics.
    }

    public function test_reading_a_topic_does_not_change_its_content_modification_time(): void
    {
        $topic = $this->topic($this->section('discussion'));
        $this->createPost($topic);
        $modifiedAt = $topic->updated_at->toAtomString();
        $this->get(route('forum.topic', $topic->id))->assertOk();
        $this->assertSame(1, $topic->fresh()->views_count);
        $this->assertSame($modifiedAt, $topic->fresh()->updated_at->toAtomString());
    }

    private function section(string $slug, bool $active = true, ?ForumSection $parent = null): ForumSection
    {
        return ForumSection::query()->create(['name' => $slug, 'slug' => $slug, 'is_active' => $active, 'parent_id' => $parent?->id]);
    }

    private function topic(ForumSection $section): ForumTopic
    {
        $topic = ForumTopic::query()->create(['section_id' => $section->id, 'author_name' => 'Автор', 'title' => 'Тема']);
        $topic->forceFill(['created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3), 'last_post_at' => now()->subDays(3)])->save();

        return $topic;
    }

    private function createPost(ForumTopic $topic): ForumPost
    {
        $post = ForumPost::query()->create(['topic_id' => $topic->id, 'author_name' => 'Автор', 'body' => '<p>Сообщение</p>']);
        $post->forceFill(['created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)])->save();

        return $post;
    }

    /** @return list<string> */
    private function locations(string $xml): array
    {
        $document = simplexml_load_string($xml);
        $this->assertNotFalse($document);
        $document->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        return array_map('strval', $document->xpath('//s:loc'));
    }
}
