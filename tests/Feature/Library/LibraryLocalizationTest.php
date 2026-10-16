<?php

declare(strict_types=1);

namespace Tests\Feature\Library;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Share\Application\Services\ContentTranslationEditor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class LibraryLocalizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        app()->setLocale('ru');

        Schema::create('library_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('library_articles', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_migration_preserves_legacy_library_content_and_model_reads_locale(): void
    {
        DB::table('library_categories')->insert(['id' => 7, 'name' => 'Профессии', 'description' => 'Описание']);
        DB::table('library_articles')->insert([
            'id' => 11,
            'title' => 'Травник',
            'excerpt' => 'О сборе трав',
            'content' => '<p>Ищите растения.</p>',
        ]);

        $migration = require database_path('migrations/2026_10_12_330000_localize_library_content.php');
        $migration->up();

        $this->assertSame('Профессии', DB::table('library_categories')->where('id', 7)->value('name_legacy'));
        $this->assertSame(['ru' => 'Травник'], json_decode(DB::table('library_articles')->where('id', 11)->value('title'), true));
        $this->assertSame('<p>Ищите растения.</p>', DB::table('library_articles')->where('id', 11)->value('content_legacy'));

        $article = LibraryArticle::query()->findOrFail(11);
        $article->setTranslation('title', 'uk', 'Травникар');
        $article->setTranslation('content', 'uk', '<p>Шукайте рослини.</p>');
        $article->save();

        app()->setLocale('uk');
        $this->assertSame('Травникар', $article->fresh()->title);
        $this->assertSame('<p>Шукайте рослини.</p>', $article->fresh()->content);
        $this->assertSame('О сборе трав', $article->fresh()->excerpt);
    }

    public function test_translation_editor_keeps_russian_and_ukrainian_library_fields_separate(): void
    {
        $category = new LibraryCategory;
        $request = Request::create('/', 'POST', [
            'name' => 'Профессии',
            'description' => 'Игровые профессии',
            'translations' => ['uk' => ['name' => 'Професії', 'description' => 'Ігрові професії']],
        ]);

        app(ContentTranslationEditor::class)->fill($category, $request, ['name', 'description']);

        $this->assertSame('Профессии', $category->getTranslation('name', 'ru'));
        $this->assertSame('Професії', $category->getTranslation('name', 'uk'));
        $this->assertSame('Ігрові професії', $category->getTranslation('description', 'uk'));
    }
}
