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
        'auto_loot_enabled' => __('ui.auto_loot_hint'),
        'weather_effects' => __('ui.weather_effects_hint'),
        'hidden_players_maps' => __('ui.hidden_players_maps_hint'),
        'hotkeys' => __('ui.hotkeys_hint'),
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
        <h2>{{ __('ui.loot') }}</h2>
        <div class="game-settings-grid">
            @if($autoLootAvailable ?? false)
                <input type="hidden" name="auto_loot_enabled" value="0">
            @endif
            @include('interface::partials.settings-checkbox', [
                'name' => 'auto_loot_enabled', 'label' => __('ui.auto_loot'),
                'checked' => ($autoLootAvailable ?? false) && (bool) old('auto_loot_enabled', $gameSettings['auto_loot_enabled'] ?? true),
                'disabled' => !($autoLootAvailable ?? false),
            ])
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
            <input type="hidden" name="weather_effects" value="0">
            @include('interface::partials.settings-checkbox', ['name' => 'weather_effects', 'label' => __('ui.weather_effects'), 'checked' => (bool) old('weather_effects', $gameSettings['weather_effects'] ?? true)])
        </div>
    </section>
    <?php
        $hotkeyDefaults = \App\Modules\User\Domain\Settings\HotkeyActions::defaults();
        $hotkeyValues = \App\Modules\User\Domain\Settings\HotkeyActions::normalize(old('hotkeys', $gameSettings['hotkeys'] ?? []));
        $hotkeyLabel = static fn (string $action): string => str_starts_with($action, 'hotbar_')
            ? __('ui.hotkey_hotbar', ['n' => substr($action, 7)])
            : __('ui.hotkey_'.$action);
    ?>
    <section class="game-settings-section" id="hotkeys-settings" data-defaults='@json($hotkeyDefaults)' data-forbidden='@json(\App\Modules\User\Domain\Settings\HotkeyActions::forbidden())'
             data-msg-press="{{ __('ui.hotkeys_press_key') }}" data-msg-taken="{{ __('ui.hotkeys_taken') }}">
        <h2>{{ __('ui.hotkeys') }}</h2>
        <p class="game-settings-note" data-setting-hint="{{ $settingHints['hotkeys'] }}">{{ __('ui.hotkeys_note') }}</p>
        <p class="game-settings-note" id="hotkeys-message" role="status" style="min-height:1.2em"></p>
        <div class="game-settings-grid">
            @foreach($hotkeyDefaults as $action => $default)
                <div class="game-settings-control hotkey-row" data-action="{{ $action }}" data-label="{{ $hotkeyLabel($action) }}">
                    <label for="hotkey-{{ $action }}">{{ $hotkeyLabel($action) }}</label>
                    <input type="hidden" name="hotkeys[{{ $action }}]" value="{{ $hotkeyValues[$action] }}">
                    <button type="button" id="hotkey-{{ $action }}" class="settings-sound-preview hotkey-button"></button>
                    <button type="button" class="settings-sound-preview hotkey-reset" title="{{ __('ui.hotkeys_reset') }}" aria-label="{{ __('ui.hotkeys_reset') }}">&#8634;</button>
                </div>
            @endforeach
        </div>
        <?php
            $magicHotkeyValues = \App\Modules\User\Domain\Settings\HotkeyActions::normalizeMagic(old('magic_hotkeys', $gameSettings['magic_hotkeys'] ?? []));
        ?>
        <h2 style="margin-top:10px">{{ __('ui.hotkeys_magic') }}</h2>
        <p class="game-settings-note">{{ __('ui.hotkeys_magic_note') }}</p>
        @if(empty($magicSkills))
            <p class="game-settings-note">{{ __('ui.hotkeys_magic_empty') }}</p>
        @else
            <div class="game-settings-grid">
                @foreach($magicSkills as $magicSkill)
                    <div class="game-settings-control hotkey-row" data-action="magic:{{ $magicSkill['id'] }}" data-label="{{ $magicSkill['name'] }}">
                        <label for="hotkey-magic-{{ $magicSkill['id'] }}">{{ $magicSkill['name'] }}</label>
                        <input type="hidden" name="magic_hotkeys[{{ $magicSkill['id'] }}]" value="{{ $magicHotkeyValues[$magicSkill['id']] ?? '' }}">
                        <button type="button" id="hotkey-magic-{{ $magicSkill['id'] }}" class="settings-sound-preview hotkey-button"></button>
                        <button type="button" class="settings-sound-preview hotkey-reset" title="{{ __('ui.hotkeys_reset') }}" aria-label="{{ __('ui.hotkeys_reset') }}">&#8634;</button>
                    </div>
                @endforeach
            </div>
        @endif
        <p class="hotkeys-reset-all"><span class="butt1 pointer"><span><input type="button" id="hotkeys-reset-all" value="{{ __('ui.hotkeys_reset_all') }}"></span></span> <span class="hotkeys-reset-note">{{ __('ui.hotkeys_save_reminder') }}</span></p>
    </section>
    <script>
        (function () {
            var root = document.getElementById('hotkeys-settings');
            if (!root) return;
            var defaults = JSON.parse(root.dataset.defaults);
            var forbidden = JSON.parse(root.dataset.forbidden);
            var message = document.getElementById('hotkeys-message');
            var rows = Array.prototype.slice.call(root.querySelectorAll('.hotkey-row'));
            var names = { ArrowUp: '↑', ArrowDown: '↓', ArrowLeft: '←', ArrowRight: '→', PageUp: 'PgUp', PageDown: 'PgDn', Space: 'Space', Minus: '-', Equal: '=',
                BracketLeft: '[', BracketRight: ']', Semicolon: ';', Quote: "'", Backquote: '`', Backslash: '\\', Comma: ',', Period: '.', Slash: '/' };

            function codeLabel(code) {
                if (names[code]) return names[code];
                if (/^Key[A-Z]$/.test(code)) return code.slice(3);
                if (/^Digit[0-9]$/.test(code)) return code.slice(5);
                if (/^Numpad/.test(code)) return 'Num ' + code.slice(6);
                return code || '—';
            }
            function input(row) { return row.querySelector('input[type="hidden"]'); }
            function button(row) { return row.querySelector('.hotkey-button'); }
            function render() {
                rows.forEach(function (row) {
                    button(row).textContent = codeLabel(input(row).value);
                });
            }
            function ownerOf(code, exceptRow) {
                if (!code) return undefined;
                return rows.find(function (row) { return row !== exceptRow && input(row).value === code; });
            }
            function say(text) { message.textContent = text || ''; }

            function assign(row, code) {
                var owner = ownerOf(code, row);
                if (owner) {
                    say(root.dataset.msgTaken.replace(':action', owner.dataset.label));
                    return false;
                }
                input(row).value = code;
                say('');
                render();
                return true;
            }

            rows.forEach(function (row) {
                var btn = button(row);
                var recording = false;
                var swallowSpaceKeyup = false;
                // Пробел, назначенный клавишей, не должен на keyup «нажать» кнопку и снова запустить запись.
                btn.addEventListener('keyup', function (event) {
                    if (event.code === 'Space' && swallowSpaceKeyup) { swallowSpaceKeyup = false; event.preventDefault(); }
                });
                function stop() {
                    recording = false;
                    btn.removeEventListener('keydown', onKey, true);
                    render();
                }
                function onKey(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (event.code === 'Escape') { say(''); stop(); return; }
                    if (event.ctrlKey || event.altKey || event.metaKey || forbidden.indexOf(event.code) !== -1 || !event.code) return;
                    if (event.code === 'Space') swallowSpaceKeyup = true;
                    if (assign(row, event.code)) stop();
                }
                btn.addEventListener('click', function () {
                    if (recording) { stop(); return; }
                    recording = true;
                    say('');
                    btn.textContent = root.dataset.msgPress;
                    btn.addEventListener('keydown', onKey, true);
                });
                btn.addEventListener('blur', function () { if (recording) stop(); });
                row.querySelector('.hotkey-reset').addEventListener('click', function () {
                    var code = defaults[row.dataset.action] || '';
                    var owner = ownerOf(code, row);
                    if (owner) { say(root.dataset.msgTaken.replace(':action', owner.dataset.label)); return; }
                    assign(row, code);
                });
            });
            document.getElementById('hotkeys-reset-all').addEventListener('click', function () {
                rows.forEach(function (row) { input(row).value = defaults[row.dataset.action] || ''; });
                say('');
                render();
            });
            render();
        })();
    </script>
    @if(!empty($visibilityMaps))
        @php($hiddenPlayersMaps = array_map('intval', (array) old('hidden_players_maps', $gameSettings['hidden_players_maps'] ?? [])))
        <section class="game-settings-section">
            <h2>{{ __('ui.hidden_players_maps') }}</h2>
            <p class="game-settings-note" data-setting-hint="{{ $settingHints['hidden_players_maps'] }}">{{ __('ui.hidden_players_maps_note') }}</p>
            <div class="game-settings-grid">
                <input type="hidden" name="hidden_players_maps[]" value="0">
                @foreach($visibilityMaps as $mapId => $mapName)
                    <div class="game-settings-control">
                        <label for="setting-hidden-players-map-{{ $mapId }}">{{ $mapName }}</label>
                        <input class="game-settings-checkbox" id="setting-hidden-players-map-{{ $mapId }}" name="hidden_players_maps[]" type="checkbox" value="{{ $mapId }}" @checked(in_array((int) $mapId, $hiddenPlayersMaps, true))>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    <div class="game-settings-actions">
        <span class="butt1 pointer"><span><input type="submit" value="{{ __('ui.save_changes') }}"></span></span>
        <p class="profile-preview-note">{{ __('ui.settings_note') }}</p>
    </div>
</form>
