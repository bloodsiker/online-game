<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\User;

use App\Modules\User\Application\Services\GameSettingsService;
use App\Modules\User\Domain\Settings\GameSettingsDefinition;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Modules\User\Infrastructure\Persistence\Models\UserGameSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class GameSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite' || DB::connection()->getDatabaseName() !== ':memory:') {
            $this->markTestSkipped('Тест разрешён только в SQLite :memory:.');
        }
        config(['cache.default' => 'array', 'session.driver' => 'array']);
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('password')->nullable();
        });
        (require database_path('migrations/2026_10_10_010000_create_user_game_settings_table.php'))->up();
        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
    }

    private function signIn(): void
    {
        $this->actingAs(User::query()->without('player')->findOrFail(1))->withSession(['_token' => 'settings-token']);
    }

    public function test_defaults_show_map_without_creating_database_rows(): void
    {
        $this->assertSame(GameSettingsDefinition::defaults(), app(GameSettingsService::class)->get(userId: 1));
        $this->assertSame(0, UserGameSetting::query()->count());
        $this->signIn();
        $this->get(route('settings'))->assertOk()->assertViewHas('gameSettings', GameSettingsDefinition::defaults());
    }

    public function test_language_is_saved_validated_and_preserved_by_partial_updates(): void
    {
        $this->signIn();
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'language' => 'uk'])
            ->assertOk()->assertJsonPath('settings.language', 'uk');
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'volume' => 30])
            ->assertOk()->assertJsonPath('settings.language', 'uk');
        foreach (['en', '<script>', '', null, []] as $language) {
            $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'language' => $language])
                ->assertUnprocessable()->assertJsonValidationErrors('language');
        }
        $this->get(route('settings'))->assertOk()->assertSee('name="language"', false)->assertSee('Українська');
        $this->assertSame('uk', app(GameSettingsService::class)->get(userId: 1)['language']);
        $this->assertSame('ru', app(GameSettingsService::class)->get(userId: 2)['language']);
    }

    public function test_save_map_setting_is_scoped_to_owner_and_applies_through_session(): void
    {
        $this->signIn();
        $this->post(route('settings.update'), [
            '_token' => 'settings-token', 'show_map' => '0', 'user_id' => 2, 'unknown_setting' => '0',
        ])->assertRedirect(route('settings'))->assertSessionHas('message', 'Настройки сохранены.')
            ->assertSessionHas('reload_game_after_settings', true);
        $stored = UserGameSetting::query()->sole();
        $this->assertSame(1, $stored->user_id);
        $expected = array_replace(GameSettingsDefinition::defaults(), ['show_map' => false]);
        $this->assertSame($expected, $stored->settings);
        $this->assertSame(GameSettingsDefinition::defaults(), app(GameSettingsService::class)->get(userId: 2));
        $this->get(route('settings'))->assertOk()->assertViewHas('gameSettings', $expected)
            ->assertSee('window.top.location.reload()', false);
        $this->assertSame('Настройки сохранены.', session('message'));
    }

    public function test_json_save_partial_updates_and_reenable_map(): void
    {
        $this->signIn();
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'show_map' => false])
            ->assertOk()->assertJsonPath('settings.show_map', false);
        $this->postJson(route('settings.update'), ['_token' => 'settings-token'])
            ->assertOk()->assertJsonPath('settings.show_map', false);
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'show_map' => true])
            ->assertOk()->assertJsonPath('settings.show_map', true);
        $this->assertSame(1, UserGameSetting::query()->count());
    }

    public function test_invalid_values_guest_and_missing_csrf_cannot_write(): void
    {
        $this->withSession(['_token' => 'settings-token'])->postJson(route('settings.update'), ['_token' => 'settings-token', 'show_map' => false])->assertUnauthorized();
        $this->signIn();
        $this->postJson(route('settings.update'), ['show_map' => false])->assertStatus(419);
        foreach ([null, 'anything', 2, []] as $value) {
            $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'show_map' => $value])
                ->assertUnprocessable()->assertJsonValidationErrors('show_map');
        }
        $this->assertSame(0, UserGameSetting::query()->count());
    }

    public function test_map_side_is_saved_and_invalid_values_are_rejected(): void
    {
        $this->assertSame('right', app(GameSettingsService::class)->get(userId: 1)['map_side']);
        $this->signIn();
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'map_side' => 'left'])->assertSuccessful();
        $this->assertSame('left', app(GameSettingsService::class)->get(userId: 1)['map_side']);
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'map_side' => 'bottom'])->assertUnprocessable()->assertJsonValidationErrors('map_side');
        $this->assertSame('right', GameSettingsDefinition::normalize(['map_side' => 'invalid'])['map_side']);
    }

    public function test_sidebar_is_rendered_on_the_selected_side_only_once(): void
    {
        $source = file_get_contents(app_path('Modules/Interface/Presentation/Views/index.blade.php'));
        $leftStart = strpos($source, "@if((\$gameSettings['show_map'] ?? true) && (\$gameSettings['map_side'] ?? 'right') === 'left')");
        $gameStart = strpos($source, '<td id="game-panel"');
        $rightStart = strpos($source, '{{-- Optional sidebar map --}}');
        $rightEnd = strpos($source, '{{-- End sidebar map --}}', $rightStart);
        $this->assertLessThan($gameStart, $leftStart);
        $this->assertLessThan($rightStart, $gameStart);
        $template = substr($source, $leftStart, $gameStart - $leftStart).'<td id="game-panel"></td>'.substr($source, $rightStart, $rightEnd - $rightStart);
        foreach (['left', 'right'] as $side) {
            $html = Blade::render($template, ['gameSettings' => ['show_map' => true, 'map_side' => $side]]);
            $this->assertSame(1, substr_count($html, 'id="map-frame"'));
            $this->assertSame($side === 'left', strpos($html, 'id="td-map"') < strpos($html, 'id="game-panel"'));
            $hidden = Blade::render($template, ['gameSettings' => ['show_map' => false, 'map_side' => $side]]);
            $this->assertStringNotContainsString('id="map-frame"', $hidden);
        }
    }

    public function test_disabled_map_is_not_rendered_including_its_sidebar_and_toggle(): void
    {
        $source = file_get_contents(app_path('Modules/Interface/Presentation/Views/index.blade.php'));
        $start = strpos($source, '{{-- Optional sidebar map --}}');
        $end = strpos($source, '{{-- End sidebar map --}}', $start);
        $template = substr($source, $start, $end - $start);
        $hidden = Blade::render($template, ['gameSettings' => ['show_map' => false]]);
        $visible = Blade::render($template, ['gameSettings' => ['show_map' => true]]);
        foreach (['id="map-frame"', 'id="td-map"', 'map-toggle-btn'] as $element) {
            $this->assertStringNotContainsString($element, $hidden);
            $this->assertStringContainsString($element, $visible);
        }
    }

    public function test_hotbar_and_tooltip_settings_save_and_survive_partial_updates(): void
    {
        $this->signIn();
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'show_hotbar' => false, 'show_item_tooltips' => false])
            ->assertOk()->assertJsonPath('settings.show_hotbar', false)->assertJsonPath('settings.show_item_tooltips', false);
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'show_map' => false])
            ->assertOk()->assertJsonPath('settings.show_hotbar', false)->assertJsonPath('settings.show_item_tooltips', false);
        $this->assertSame(array_replace(GameSettingsDefinition::defaults(), ['show_map' => false, 'show_hotbar' => false, 'show_item_tooltips' => false]), app(GameSettingsService::class)->get(userId: 1));
        foreach (['show_hotbar', 'show_item_tooltips'] as $key) {
            $this->postJson(route('settings.update'), ['_token' => 'settings-token', $key => 'invalid'])
                ->assertUnprocessable()->assertJsonValidationErrors($key);
        }
    }

    public function test_notification_sounds_are_saved_validated_and_rendered(): void
    {
        $this->signIn();
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'chat_sound_enabled' => '1', 'volume' => '75', 'sound_private' => '6'])
            ->assertOk()->assertJsonPath('settings.chat_sound_enabled', true)->assertJsonPath('settings.volume', 75)->assertJsonPath('settings.sound_private', 6);
        $this->get(route('settings'))->assertOk()->assertSee('value="75"', false)
            ->assertSee('data-sound-preview="sound_private"', false)->assertSee('Уведомление №6')->assertDontSee('Звук №10');
        foreach (['volume' => 101, 'sound_private' => 7, 'sound_clan' => -1, 'sound_mention' => 'bad', 'sound_trade' => 1.5] as $key => $value) {
            $this->postJson(route('settings.update'), ['_token' => 'settings-token', $key => $value])
                ->assertUnprocessable()->assertJsonValidationErrors($key);
        }
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'chat_sound_enabled' => false, 'volume' => 0])
            ->assertOk()->assertJsonPath('settings.chat_sound_enabled', false)->assertJsonPath('settings.volume', 0)->assertJsonPath('settings.sound_private', 6);
    }

    public function test_game_sound_selections_and_volume_are_saved_and_can_be_disabled(): void
    {
        $this->signIn();
        $keys = ['mute_open_chat', 'sound_battle', 'sound_dungeon', 'sound_mail', 'sound_gathering'];
        $values = array_replace(array_fill_keys($keys, '6'), ['mute_open_chat' => '1']);
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'volume' => 100, ...$values])->assertOk();
        $settings = app(GameSettingsService::class)->get(userId: 1);
        foreach ($keys as $key) {
            $this->assertSame($key === 'mute_open_chat' ? true : 6, $settings[$key]);
            $this->postJson(route('settings.update'), ['_token' => 'settings-token', $key => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors($key);
        }
        $this->get(route('settings'))->assertOk()->assertDontSee('пока не подключены');
        $this->postJson(route('settings.update'), ['_token' => 'settings-token', 'volume' => 0, ...array_fill_keys($keys, '0')])->assertOk();
        foreach ($keys as $key) {
            $this->assertSame($key === 'mute_open_chat' ? false : 0, app(GameSettingsService::class)->get(userId: 1)[$key]);
        }
    }

    public function test_legacy_event_checkboxes_keep_their_previous_sounds(): void
    {
        UserGameSetting::query()->create(['user_id' => 1, 'settings' => [
            'sound_battle' => true, 'sound_dungeon' => true, 'sound_mail' => true, 'sound_gathering' => false,
        ]]);
        $settings = app(GameSettingsService::class)->get(userId: 1);
        $this->assertSame(1, $settings['sound_battle']);
        $this->assertSame(2, $settings['sound_dungeon']);
        $this->assertSame(3, $settings['sound_mail']);
        $this->assertSame(0, $settings['sound_gathering']);
        $this->signIn();
        foreach (['sound_battle', 'sound_dungeon', 'sound_mail', 'sound_gathering'] as $key) {
            $this->postJson(route('settings.update'), ['_token' => 'settings-token', $key => 7])->assertUnprocessable()->assertJsonValidationErrors($key);
        }
        $this->get(route('settings'))->assertOk()->assertSee('data-sound-preview="sound_dungeon"', false);
    }

    public function test_disabled_hotbar_has_no_markup_or_loading_scripts(): void
    {
        $source = file_get_contents(app_path('Modules/Interface/Presentation/Views/index.blade.php'));
        $start = strpos($source, '{{-- Optional hotbar --}}');
        $end = strpos($source, '{{-- End hotbar --}}', $start);
        $template = substr($source, $start, $end - $start);
        $hidden = Blade::render($template, ['gameSettings' => ['show_hotbar' => false]]);
        $visible = Blade::render($template, ['gameSettings' => ['show_hotbar' => true]]);
        foreach (['id="hotbar"', 'id="hotbar-slots"', 'gameFrame.addEventListener(\'load\', refreshHotbar)'] as $element) {
            $this->assertStringNotContainsString($element, $hidden);
            $this->assertStringContainsString($element, $visible);
        }
        $this->assertStringContainsString('window.refreshHotbar = () => {}', $hidden);
    }
}
