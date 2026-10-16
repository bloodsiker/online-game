@php
    $settingHints = [
        'language' => __('ui.language_hint'),
        'chat_sound_enabled' => __('ui.chat_sound_enabled_hint'),
        'volume' => __('ui.volume_hint'),
        'sound_mention' => __('ui.sound_mention_hint'),
        'sound_private' => __('ui.sound_private_hint'),
        'sound_clan' => __('ui.sound_clan_hint'),
        'sound_trade' => __('ui.sound_trade_hint'),
        'mute_open_chat' => __('ui.mute_open_chat_hint'),
        'sound_battle' => __('ui.sound_battle_hint'),
        'sound_dungeon' => __('ui.sound_dungeon_hint'),
        'sound_mail' => __('ui.sound_mail_hint'),
        'sound_gathering' => __('ui.sound_gathering_hint'),
        'show_hotbar' => __('ui.show_hotbar_hint'),
        'show_map' => __('ui.show_map_hint'),
        'map_side' => __('ui.map_side_hint'),
        'show_item_tooltips' => __('ui.show_item_tooltips_hint'),
    ];
    foreach ($settingHints as $key => &$hint) {
        if (!array_key_exists($key, \App\Modules\User\Domain\Settings\GameSettingsDefinition::defaults())) {
            $hint .= ' Сохранение и применение этой настройки пока не подключены.';
        }
    }
    unset($hint);
@endphp
<form class="game-settings" method="post" action="{{ route('settings.update') }}">
    @csrf
    @if(isset($errors) && $errors->any())
        <div role="alert" style="color:#BA0000">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif
    <section class="game-settings-section">
        <h2>{{ __('ui.language') }}</h2>
        <div class="game-settings-grid">
            @include('interface::partials.settings-select', ['name' => 'language', 'label' => __('ui.language'), 'options' => ['ru' => 'Русский', 'uk' => 'Українська'], 'selected' => old('language', $gameSettings['language'] ?? 'ru')])
        </div>
    </section>
    <section class="game-settings-section">
        <h2>{{ __('ui.chat') }}</h2>
        <div class="game-settings-grid">
            <input type="hidden" name="chat_sound_enabled" value="0">
            @foreach([
                ['name' => 'chat_sound_enabled', 'label' => __('ui.chat_sound_enabled')],
            ] as $setting)
                @include('interface::partials.settings-checkbox', array_merge($setting, ['checked' => (bool) old($setting['name'], $gameSettings[$setting['name']] ?? false)]))
            @endforeach
            <div class="game-settings-control" data-setting-hint="{{ $settingHints['volume'] }}">
                <label for="setting-volume">{{ __('ui.volume') }}</label>
                <input id="setting-volume" name="volume" class="game-settings-volume" type="range" min="0" max="100" value="{{ old('volume', $gameSettings['volume'] ?? 50) }}">
            </div>
            @php($sounds = [0 => __('ui.no_sound')])
            @for($sound = 1; $sound <= 6; $sound++)
                @php($sounds[$sound] = __('ui.notification', ['number' => $sound]))
            @endfor
            @foreach(['sound_mention' => __('ui.sound_mention'), 'sound_private' => __('ui.sound_private'), 'sound_clan' => __('ui.clan'), 'sound_trade' => __('ui.sound_trade')] as $name => $label)
                @include('interface::partials.settings-select', ['name' => $name, 'label' => $label, 'options' => $sounds, 'selected' => old($name, $gameSettings[$name] ?? 0), 'soundPreview' => true])
            @endforeach
            <input type="hidden" name="mute_open_chat" value="0">
            @include('interface::partials.settings-checkbox', ['name' => 'mute_open_chat', 'label' => __('ui.mute_open_chat'), 'checked' => (bool) old('mute_open_chat', $gameSettings['mute_open_chat'] ?? false)])
        </div>
    </section>
    <section class="game-settings-section">
        <h2>{{ __('ui.event_sounds') }}</h2>
        <div class="game-settings-grid">
            @foreach([
                ['name' => 'sound_battle', 'label' => __('ui.sound_battle')],
                ['name' => 'sound_dungeon', 'label' => __('ui.sound_dungeon')],
                ['name' => 'sound_mail', 'label' => __('ui.sound_mail')],
                ['name' => 'sound_gathering', 'label' => __('ui.sound_gathering')],
            ] as $setting)
                @include('interface::partials.settings-select', ['name' => $setting['name'], 'label' => $setting['label'], 'options' => $sounds, 'selected' => old($setting['name'], $gameSettings[$setting['name']] ?? 0), 'soundPreview' => true])
            @endforeach
        </div>
    </section>
    <section class="game-settings-section">
        <h2>{{ __('ui.security') }}</h2>
        <div class="game-settings-security" data-setting-hint="{{ __('ui.password_hint') }}">
            <span class="butt1 pointer"><span><input type="button" value="{{ __('ui.change_password') }}" id="change-password-open"></span></span>
        </div>
    </section>
    <section class="game-settings-section">
        <h2>{{ __('ui.interface') }}</h2>
        <div class="game-settings-grid">
            <input type="hidden" name="show_map" value="0">
            @include('interface::partials.settings-checkbox', ['name' => 'show_map', 'label' => __('ui.show_map'), 'checked' => (bool) old('show_map', $gameSettings['show_map'] ?? true)])
            <input type="hidden" name="show_hotbar" value="0">
            @include('interface::partials.settings-checkbox', ['name' => 'show_hotbar', 'label' => __('ui.show_hotbar'), 'checked' => (bool) old('show_hotbar', $gameSettings['show_hotbar'] ?? true)])
            @include('interface::partials.settings-select', ['name' => 'map_side', 'label' => __('ui.map_side'), 'options' => ['right' => __('ui.map_right'), 'left' => __('ui.map_left')], 'selected' => old('map_side', $gameSettings['map_side'] ?? 'right')])
            <input type="hidden" name="show_item_tooltips" value="0">
            @include('interface::partials.settings-checkbox', ['name' => 'show_item_tooltips', 'label' => __('ui.show_item_tooltips'), 'checked' => (bool) old('show_item_tooltips', $gameSettings['show_item_tooltips'] ?? true)])
        </div>
    </section>
    <div class="game-settings-actions">
        <span class="butt1 pointer"><span><input type="submit" value="{{ __('ui.save_changes') }}"></span></span>
        <p class="profile-preview-note">{{ __('ui.settings_note') }}</p>
    </div>
</form>
