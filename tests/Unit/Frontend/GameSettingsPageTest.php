<?php

declare(strict_types=1);

namespace Tests\Unit\Frontend;

use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

final class GameSettingsPageTest extends TestCase
{
    public function test_settings_pages_have_tabs_and_matching_placeholders(): void
    {
        foreach (['game' => 'name="volume"', 'profile' => 'name="real_name"'] as $tab => $message) {
            $html = view('interface::settings', ['tab' => $tab, 'errors' => new ViewErrorBag])->render();

            $this->assertStringContainsString($message, $html);
            $this->assertStringNotContainsString('Предпросмотр настроек. Сохранение и применение будут добавлены позже.', $html);
            $this->assertStringContainsString(route('settings'), $html);
            $this->assertStringContainsString(route('settings.profile'), $html);
            $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        }
    }

    public function test_settings_routes_require_authentication(): void
    {
        foreach (['settings', 'settings.profile'] as $name) {
            $route = app('router')->getRoutes()->getByName($name);

            $this->assertNotNull($route);
            $this->assertContains('auth', $route->gatherMiddleware());
        }
    }

    public function test_profile_has_fields_and_save_endpoint(): void
    {
        $html = view('interface::settings', ['tab' => 'profile', 'errors' => new ViewErrorBag])->render();
        foreach (['real_name', 'birth_day', 'birth_month', 'birth_year', 'residence', 'motto', 'about'] as $name) {
            $this->assertStringContainsString('name="'.$name.'"', $html);
        }
        $this->assertStringContainsString('type="submit" value="Сохранить"', $html);
        $this->assertStringNotContainsString('chat_color', $html);
        $this->assertStringContainsString('<input id="profile-residence" name="residence" type="text"', $html);
        $this->assertStringContainsString('action="'.route('settings.profile.update').'"', $html);
        $this->assertStringNotContainsString('https://dwar.ltd', $html);
    }

    public function test_canvas_and_fallback_menu_both_link_to_settings(): void
    {
        $xml = simplexml_load_file(public_path('data/locale/ru/topMenu.xml'));
        $items = $xml->xpath('/data/item[@id="3"]/item[@command="m_settings"]');
        $this->assertCount(1, $items);
        $this->assertSame('26', (string) $items[0]['id']);
        $this->assertCount(1, $xml->xpath('/data/item[@id="3"]/item[@command="m_professions"]/following-sibling::item[1][@command="m_settings"]'));
        $this->assertCount(0, $xml->xpath('/data/item[@command="m_settings"]'));

        $menu = view('interface::menu')->render();
        $this->assertStringContainsString(route('settings'), $menu);
        $this->assertStringContainsString('m_settings:', $menu);
        $this->assertStringContainsString('>Настройки</a>', $menu);
        $atlas = json_decode(file_get_contents(public_path('data/canvas/ui/main.json')), true);
        $this->assertArrayHasKey('top/form.png', $atlas['frames']);
    }

    public function test_settings_form_has_sections_and_active_save_button(): void
    {
        $html = view('interface::settings', ['tab' => 'game'])->render();
        foreach (['Чат', 'Звуки событий', 'Безопасность аккаунта', 'Интерфейс'] as $heading) {
            $this->assertStringContainsString('<h2>'.$heading.'</h2>', $html);
        }
        $this->assertStringNotContainsString('<h2>Инвентарь</h2>', $html);
        foreach (['show_use_button', 'show_sell_button', 'show_delete_button', 'use_in_frame'] as $name) {
            $this->assertStringNotContainsString('name="'.$name.'"', $html);
        }
        $this->assertStringContainsString('name="volume"', $html);
        $this->assertStringContainsString('--volume-fill', $html);
        $this->assertStringContainsString('volume.addEventListener(\'input\', updateVolumeFill)', $html);
        $this->assertStringContainsString('background: #fff1c5', $html);
        $this->assertStringNotContainsString('<h2>Бой</h2>', $html);
        foreach (['battle_hotkeys', 'hide_battle_log', 'battle_slots', 'quick_backpack'] as $name) {
            $this->assertStringNotContainsString('name="'.$name.'"', $html);
        }
        $this->assertStringContainsString('type="submit" value="Сохранить изменения"', $html);
        $this->assertStringContainsString('action="'.route('settings.update').'"', $html);
        $this->assertStringContainsString('js/profile-select.js', $html);
        $this->assertStringContainsString(route('settings.password.update'), $html);
        $this->assertStringContainsString('name="current_password"', $html);
        $this->assertStringContainsString('name="password_confirmation"', $html);
        $this->assertStringContainsString('name="_token"', $html);
    }

    public function test_removed_chat_and_sound_settings_are_not_rendered(): void
    {
        $html = view('interface::settings', ['tab' => 'game'])->render();
        foreach ([
            'fixed_menu' => 'Зафиксировать верхнее меню',
            'location_chat' => 'Чат в пределах локации',
            'disable_location_music' => 'Отключить музыку локаций',
            'sound_pause' => 'Пауза между звуками',
            'repeat_private' => 'Повторять приват',
            'hide_auxiliary_messages' => 'Отключить вспомогательные системные сообщения',
            'show_all_players' => 'Игроки на всех картах',
        ] as $name => $label) {
            $this->assertStringNotContainsString('name="'.$name.'"', $html);
            $this->assertStringNotContainsString($label, $html);
        }
    }

    public function test_every_game_setting_has_an_explanation(): void
    {
        $html = view('interface::settings', ['tab' => 'game'])->render();
        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$html);
        $xpath = new \DOMXPath($document);
        $controls = $xpath->query('//div[@class="game-settings-control" or @class="game-settings-security"]');
        $this->assertSame(17, $controls->length);
        foreach ($controls as $control) {
            $this->assertNotEmpty($control->getAttribute('data-setting-hint'));
        }
        $this->assertStringContainsString('js/settings-tooltips.js', $html);
        $this->assertStringNotContainsString('Сохранение и применение этой настройки пока не подключены.', $html);
        $map = $xpath->query('//input[@id="setting-show_map"]/..')->item(0);
        $this->assertStringNotContainsString('пока не подключены', $map->getAttribute('data-setting-hint'));
    }
}
