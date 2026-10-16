<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\User;

use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class GameLocaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Only SQLite :memory: is allowed.');
        }
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });
        (require database_path('migrations/2026_10_10_010000_create_user_game_settings_table.php'))->up();
        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
        DB::table('user_game_settings')->insert(['user_id' => 2, 'settings' => json_encode(['language' => 'uk'])]);
        Route::middleware('web')->get('/_test/locale', function (Request $request, GameSettingsService $settings) {
            if ($request->user()) {
                $settings->get((int) $request->user()->id);
            }

            return response()->json(['locale' => app()->getLocale(), 'save' => __('ui.save')]);
        });
        Route::middleware('web')->get('/_test/menu', fn () => view('interface::menu'));
    }

    private function signIn(int $id): void
    {
        $this->actingAs(User::query()->without('player')->findOrFail($id));
    }

    public function test_player_language_applies_to_pages_frames_and_ajax_without_leaking(): void
    {
        $this->signIn(2);
        $this->withSession(['language' => 'ru'])->getJson('/_test/locale')->assertOk()
            ->assertJson(['locale' => 'uk', 'save' => 'Зберегти']);
        $this->get('/_test/menu')->assertOk()->assertSee('<html lang="uk">', false)
            ->assertSee('Налаштування')->assertSee('Бібліотека');
        $this->get(route('settings'))->assertOk()->assertSee('Налаштування гри')->assertSee('Зберегти зміни')->assertSee('Змінити пароль');
        $this->signIn(1);
        $this->getJson('/_test/locale')->assertJson(['locale' => 'ru', 'save' => 'Сохранить']);
        $this->assertSame('ru', app()->getLocale());
    }

    public function test_guests_use_valid_session_language_and_invalid_values_fall_back_to_russian(): void
    {
        $this->withSession(['language' => 'uk'])->getJson('/_test/locale')->assertJsonPath('locale', 'uk');
        $this->withSession(['language' => '../../en'])->getJson('/_test/locale')->assertJsonPath('locale', 'ru');
        $this->assertSame(1, DB::table('user_game_settings')->count());
    }

    public function test_settings_are_read_once_per_request_and_saved_language_is_applied_immediately(): void
    {
        $this->signIn(1);
        DB::enableQueryLog();
        $this->getJson('/_test/locale')->assertOk();
        $queries = array_filter(DB::getQueryLog(), fn (array $query): bool => str_contains($query['query'], 'user_game_settings'));
        $this->assertCount(1, $queries);
        $this->withSession(['_token' => 'locale-token'])->post(route('settings.update'), ['_token' => 'locale-token', 'language' => 'uk'])
            ->assertRedirect(route('settings'))->assertSessionHas('message', 'Налаштування збережено.')
            ->assertSessionHas('language', 'uk')->assertSessionHas('reload_game_after_settings', true);
        $this->getJson('/_test/locale')->assertJsonPath('locale', 'uk');
        $this->get(route('settings'))->assertOk()->assertSee('Налаштування гри');
    }

    public function test_missing_ukrainian_translation_falls_back_to_russian(): void
    {
        app('translator')->addLines(['ui.test_fallback' => 'Русский резерв'], 'ru');
        app()->setLocale('uk');
        $this->assertSame('Русский резерв', __('ui.test_fallback'));
        app()->setLocale('ru');
    }
}
