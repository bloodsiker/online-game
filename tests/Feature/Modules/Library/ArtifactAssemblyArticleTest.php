<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Library;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use Database\Seeders\UpdateLibraryArtifactAssemblySeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ArtifactAssemblyArticleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Только SQLite :memory:, игровая база не используется.');
        }
        Storage::fake('local');
        Schema::create('library_articles', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->json('content');
            $table->json('excerpt')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
        });
        (require database_path('migrations/2026_10_19_000002_create_item_assemblies.php'))->up();
        DB::table('item_assemblies')->insert(['id' => 2, 'name' => '{}', 'type' => 'artifact', 'result_mode' => 'random', 'is_active' => true]);
        foreach (range(1842, 1852) as $id) {
            DB::table('share_items')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Предмет '.$id, 'uk' => 'ПредметУК '.$id])]);
            DB::table($id >= 1848 ? 'item_assembly_components' : 'item_assembly_rewards')
                ->insert(['item_assembly_id' => 2, 'share_item_id' => $id, 'count' => 1]);
        }
        LibraryArticle::query()->create([
            'slug' => 'sborka-artefaktov',
            'content' => [
                'ru' => '<p>Мой дизайн</p><h2>Первый набор осколков</h2><p>[[item:1836]] [[item:924]]</p><div class="library-info-block library-info-block--important">Правила</div>',
                'uk' => '<p>Мій дизайн</p><h2>Перший набір уламків</h2><p>[[item:1836]] [[item:924]]</p><div class="library-info-block library-info-block--important">ПравилаУК</div>',
            ],
        ]);
    }

    public function test_update_preserves_existing_content_and_adds_all_new_items_in_frames(): void
    {
        $this->seed(UpdateLibraryArtifactAssemblySeeder::class);
        $article = LibraryArticle::query()->sole();
        foreach (['ru', 'uk'] as $locale) {
            $content = $article->getTranslation('content', $locale, false);
            foreach (range(1842, 1852) as $id) {
                $this->assertStringContainsString('[[item:'.$id.']]', $content);
            }
            $this->assertStringContainsString('[[item:1836]]', $content);
            $this->assertStringContainsString('[[item:924]]', $content);
            $this->assertSame(2, substr_count($content, 'class="library-game-frame"'));
        }
        $this->assertStringContainsString('Мой дизайн', $article->getTranslation('content', 'ru'));
        $this->assertStringContainsString('Мій дизайн', $article->getTranslation('content', 'uk'));
        $backups = Storage::disk('local')->allFiles('backups/library');
        $this->assertCount(1, $backups);
        $original = json_decode(Storage::disk('local')->get($backups[0]), true, flags: JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('[[item:1848]]', $original['content']);
    }

    public function test_repeated_update_does_not_duplicate_new_section(): void
    {
        $this->seed(UpdateLibraryArtifactAssemblySeeder::class);
        $this->seed(UpdateLibraryArtifactAssemblySeeder::class);
        $content = LibraryArticle::query()->sole()->getTranslation('content', 'ru');
        $this->assertSame(1, substr_count($content, '<!-- artifact-assembly:2:start -->'));
        $this->assertSame(1, substr_count($content, '[[item:1848]]'));
    }

    public function test_rare_assembly_adds_six_shards_and_six_rewards_without_losing_existing_sections(): void
    {
        DB::table('item_assemblies')->insert(['id' => 3, 'name' => '{}', 'type' => 'artifact', 'result_mode' => 'random', 'is_active' => true]);
        foreach (range(1853, 1864) as $id) {
            DB::table('share_items')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Редкий предмет '.$id, 'uk' => 'Рідкісний предмет '.$id])]);
            DB::table($id >= 1859 ? 'item_assembly_components' : 'item_assembly_rewards')
                ->insert(['item_assembly_id' => 3, 'share_item_id' => $id, 'count' => 1]);
        }
        $this->seed(UpdateLibraryArtifactAssemblySeeder::class);
        $this->seed(UpdateLibraryArtifactAssemblySeeder::class);
        $article = LibraryArticle::query()->sole();
        foreach (['ru', 'uk'] as $locale) {
            $content = $article->getTranslation('content', $locale, false);
            foreach (range(1842, 1864) as $id) {
                $this->assertStringContainsString('[[item:'.$id.']]', $content);
            }
            $this->assertStringContainsString('[[item:1836]]', $content);
            $this->assertSame(4, substr_count($content, 'class="library-game-frame"'));
            $this->assertSame(1, substr_count($content, '<!-- artifact-assembly:2:start -->'));
            $this->assertSame(1, substr_count($content, '<!-- artifact-assembly:3:start -->'));
            $this->assertStringContainsString($locale === 'uk' ? 'Рідкісні артефакти: шість уламків' : 'Редкие артефакты: шесть осколков', $content);
        }
    }

    public function test_epic_assembly_adds_seven_shards_and_six_rewards_in_both_languages(): void
    {
        foreach ([3 => [1853, 1864, 1859], 4 => [1865, 1877, 1871]] as $assemblyId => [$first, $last, $firstShard]) {
            DB::table('item_assemblies')->insert(['id' => $assemblyId, 'name' => '{}', 'type' => 'artifact', 'result_mode' => 'random', 'is_active' => true]);
            foreach (range($first, $last) as $id) {
                DB::table('share_items')->insert(['id' => $id, 'name' => json_encode(['ru' => 'Предмет '.$id, 'uk' => 'ПредметУК '.$id])]);
                DB::table($id >= $firstShard ? 'item_assembly_components' : 'item_assembly_rewards')
                    ->insert(['item_assembly_id' => $assemblyId, 'share_item_id' => $id, 'count' => 1]);
            }
        }
        $this->seed(UpdateLibraryArtifactAssemblySeeder::class);
        $this->seed(UpdateLibraryArtifactAssemblySeeder::class);
        $article = LibraryArticle::query()->sole();
        foreach (['ru', 'uk'] as $locale) {
            $content = $article->getTranslation('content', $locale, false);
            foreach (range(1842, 1877) as $id) {
                $this->assertStringContainsString('[[item:'.$id.']]', $content);
            }
            $this->assertStringContainsString('[[item:1836]]', $content);
            $this->assertSame(6, substr_count($content, 'class="library-game-frame"'));
            foreach ([2, 3, 4] as $id) {
                $this->assertSame(1, substr_count($content, '<!-- artifact-assembly:'.$id.':start -->'));
            }
            $this->assertStringContainsString($locale === 'uk' ? 'Епічні артефакти: сім уламків' : 'Эпические артефакты: семь осколков', $content);
        }
    }
}
