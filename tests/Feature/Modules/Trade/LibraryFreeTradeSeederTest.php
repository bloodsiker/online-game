<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Trade;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use Database\Seeders\LibraryFreeTradeSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LibraryFreeTradeSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('library_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
        });
        Schema::create('library_articles', function (Blueprint $table): void {
            $table->id();
            $table->integer('category_id');
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->text('content');
            $table->string('status')->default('published');
            $table->timestamp('published_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('npcs', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('location_id');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('trade_posts', function (Blueprint $table): void {
            $table->id();
            $table->integer('npc_id');
            $table->string('name');
            $table->integer('license_price');
            $table->boolean('is_active')->default(true);
        });
        Schema::create('trade_goods', function (Blueprint $table): void {
            $table->id();
            $table->integer('trade_post_id');
            $table->integer('share_item_id');
            $table->integer('required_skill_lvl');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
        });
        DB::table('library_categories')->insert(['id' => 1, 'slug' => 'professii']);
        DB::table('library_articles')->insert(['category_id' => 2, 'slug' => 'mirnye-navyki', 'title' => 'Мирные навыки', 'content' => '<p>Авторское оформление</p>']);
        DB::table('npcs')->insert(['id' => 1, 'name' => 'Хельга', 'location_id' => 2]);
        DB::table('trade_posts')->insert(['id' => 1, 'npc_id' => 1, 'name' => 'Нейрин', 'license_price' => 100]);
        DB::table('trade_goods')->insert(['trade_post_id' => 1, 'share_item_id' => 123, 'required_skill_lvl' => 50]);
    }

    public function test_publishes_article_and_appends_link_without_replacing_existing_content(): void
    {
        $overviewArticle = LibraryArticle::query()->where('slug', 'mirnye-navyki')->firstOrFail();
        $overviewArticle->setTranslation('content', 'uk', '<p>Українське авторське оформлення</p>');
        $overviewArticle->save();

        (new LibraryFreeTradeSeeder)->run();

        $article = DB::table('library_articles')->where('slug', 'volnyy-torg')->first();
        $this->assertSame(1, (int) $article->category_id);
        $this->assertSame('published', $article->status);
        $this->assertStringContainsString('library-game-frame', $article->content);
        $this->assertStringContainsString('[[item:123]]', $article->content);
        $this->assertStringContainsString('Хельга', $article->content);
        $this->assertStringContainsString('Навык 50', $article->content);
        $overview = json_decode(DB::table('library_articles')->where('slug', 'mirnye-navyki')->value('content'), true);
        $this->assertStringStartsWith('<p>Авторское оформление</p>', $overview['ru']);
        $this->assertStringContainsString('/library/volnyy-torg?category=professii', $overview['ru']);
        $this->assertSame('<p>Українське авторське оформлення</p>', $overview['uk']);
    }

    public function test_repeated_run_does_not_duplicate_link_or_replace_manual_edits(): void
    {
        $seeder = new LibraryFreeTradeSeeder;
        $seeder->run();
        DB::table('library_articles')->where('slug', 'volnyy-torg')->update(['content' => '<p>Правка администратора</p>']);
        $seeder->run();

        $this->assertSame(1, DB::table('library_articles')->where('slug', 'volnyy-torg')->count());
        $this->assertSame('<p>Правка администратора</p>', DB::table('library_articles')->where('slug', 'volnyy-torg')->value('content'));
        $this->assertSame(1, substr_count(DB::table('library_articles')->where('slug', 'mirnye-navyki')->value('content'), '/library/volnyy-torg?category=professii'));
    }
}
