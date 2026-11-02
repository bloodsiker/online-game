<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\News;

use App\Modules\News\Infrastructure\Persistence\Models\NewsPost;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class NewsImageUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Только SQLite :memory:, игровая база не используется.');
        }
        config(['session.driver' => 'array', 'cache.default' => 'array']);
        Storage::fake('public');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_admin')->default(false);
            $table->string('active_login_token')->default('test-login');
            $table->timestamp('email_verified_at')->nullable();
        });
        Schema::create('user_game_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('settings');
            $table->timestamps();
        });
        Schema::create('news_posts', function (Blueprint $table): void {
            $table->id();
            $table->json('title');
            $table->json('description');
            $table->integer('views_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_comments')->default(true);
            $table->timestamps();
        });
        foreach ([1, 2] as $id) {
            DB::table('users')->insert(['id' => $id, 'name' => 'NewsUser'.$id, 'is_admin' => $id === 1, 'email_verified_at' => now()]);
        }
    }

    private function loginAs(int $id): static
    {
        return $this->actingAs(User::query()->without('player')->findOrFail($id))
            ->withSession(['active_login_token' => 'test-login', '_token' => 'news-test-csrf'])
            ->withHeaders(['X-CSRF-TOKEN' => 'news-test-csrf']);
    }

    public function test_admin_uploads_image_to_monthly_storage_directory(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 22));
        $response = $this->loginAs(1)->postJson(route('admin.news.upload-image'), ['file' => UploadedFile::fake()->image('news.png')]);
        $response->assertOk();
        $files = Storage::disk('public')->allFiles('news/2026/10');
        $this->assertCount(1, $files);
        $this->assertSame(Storage::disk('public')->url($files[0]), $response->json('url'));
    }

    public function test_invalid_files_are_rejected_and_players_cannot_upload(): void
    {
        $this->loginAs(1)->postJson(route('admin.news.upload-image'), ['file' => UploadedFile::fake()->create('bad.txt', 10, 'text/plain')])
            ->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->postJson(route('admin.news.upload-image'), ['file' => UploadedFile::fake()->image('big.png')->size(4097)])
            ->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->loginAs(2)->postJson(route('admin.news.upload-image'), ['file' => UploadedFile::fake()->image('test.png')])->assertRedirect();
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }

    public function test_news_saves_image_markup_in_each_language_without_overwriting_translations(): void
    {
        $html = '<p>Новость</p><img src="/storage/news/2026/10/test.png">';
        $this->loginAs(1)->post(route('admin.news.store'), [
            'title' => 'Новость', 'description' => $html,
            'translations' => ['uk' => ['title' => 'Новина', 'description' => '<p>Новина</p>']],
            'is_active' => 1, 'allow_comments' => 1,
        ])->assertRedirect();
        $news = NewsPost::query()->sole();
        $this->assertSame($html, $news->getTranslation('description', 'ru'));
        $this->assertSame('<p>Новина</p>', $news->getTranslation('description', 'uk'));
        $this->assertSame('Новость', $news->getTranslation('title', 'ru'));
        $this->get(route('admin.news.create'))->assertOk()->assertSee('Загрузить изображение')->assertSee('imageDialog.show', false);
    }

    public function test_shortcode_picker_searches_names_and_ids_with_pagination(): void
    {
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
        });
        foreach (range(1, 21) as $id) {
            // SQLite LOWER не преобразует кириллицу, в отличие от MySQL utf8mb4.
            DB::table('skills')->insert(['id' => $id, 'name' => json_encode(['ru' => 'навык '.$id, 'uk' => 'уміння '.$id])]);
        }
        $this->loginAs(1)->getJson(route('admin.news.shortcode-entities', ['type' => 'skill']))
            ->assertOk()->assertJsonCount(20, 'results')->assertJsonPath('pagination.more', true);
        $this->getJson(route('admin.news.shortcode-entities', ['type' => 'skill', 'page' => 2]))
            ->assertOk()->assertJsonCount(1, 'results')->assertJsonPath('pagination.more', false);
        $this->getJson(route('admin.news.shortcode-entities', ['type' => 'skill', 'q' => 'Уміння 21']))
            ->assertOk()->assertJsonPath('results.0.id', 21);
        $this->getJson(route('admin.news.shortcode-entities', ['type' => 'skill', 'q' => '21']))
            ->assertOk()->assertJsonPath('results.0.id', 21);
        $this->getJson(route('admin.news.shortcode-entities', ['type' => 'unknown']))->assertUnprocessable();
        $this->getJson(route('admin.news.shortcode-entities', ['type' => 'skill', 'page' => 0]))->assertUnprocessable();
    }

    public function test_signed_in_player_language_choice_is_saved_in_game_settings(): void
    {
        $this->loginAs(2)->post(route('homepage.language'), ['language' => 'uk'])
            ->assertRedirect(route('index'))->assertSessionHas('language', 'uk');
        $stored = json_decode(DB::table('user_game_settings')->where('user_id', 2)->value('settings'), true);
        $this->assertSame('uk', $stored['language']);
        $this->assertFalse(DB::table('user_game_settings')->where('user_id', 1)->exists());
    }
}
