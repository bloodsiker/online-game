@php
    $settingHints = [
        'chat_sound_enabled' => 'Включает звуковые уведомления о новых сообщениях в чате.',
        'volume' => 'Задаёт громкость звуков уведомлений и событий: от 0 до 100%.',
        'sound_mention' => 'Выбирает звук уведомления, когда другой игрок обращается к вам в чате. «Без звука» отключает уведомление.',
        'sound_private' => 'Выбирает звук для новых личных сообщений. «Без звука» отключает уведомление.',
        'sound_clan' => 'Выбирает звук для новых сообщений в клановом чате. «Без звука» отключает уведомление.',
        'sound_trade' => 'Выбирает звук для новых сообщений в торговом чате. «Без звука» отключает уведомление.',
        'mute_open_chat' => 'Отключает звуковые уведомления сообщений, пока фокус находится в чате (например, когда вы пишете сообщение).',
        'sound_battle' => 'Выбирает звук начала боя. «Без звука» отключает уведомление.',
        'sound_dungeon' => 'Выбирает звук входа в подземелье. «Без звука» отключает уведомление.',
        'sound_mail' => 'Выбирает звук новой почты. «Без звука» отключает уведомление.',
        'sound_gathering' => 'Выбирает звук успешной добычи ресурса. «Без звука» отключает уведомление.',
        'show_hotbar' => 'Показывает панель быстрого доступа к предметам и заклинаниям.',
        'show_map' => 'Показывает боковую карту в игровом интерфейсе. Снимите флажок, чтобы скрыть её. Изменение применяется после нажатия «Сохранить изменения».',
        'show_item_tooltips' => 'Показывает информацию о предмете при наведении на его изображение или название.',
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
        <h2>Чат</h2>
        <div class="game-settings-grid">
            <input type="hidden" name="chat_sound_enabled" value="0">
            @foreach([
                ['name' => 'chat_sound_enabled', 'label' => 'Включить звук уведомлений чата'],
            ] as $setting)
                @include('interface::partials.settings-checkbox', array_merge($setting, ['checked' => (bool) old($setting['name'], $gameSettings[$setting['name']] ?? false)]))
            @endforeach
            <div class="game-settings-control" data-setting-hint="{{ $settingHints['volume'] }}">
                <label for="setting-volume">Громкость</label>
                <input id="setting-volume" name="volume" class="game-settings-volume" type="range" min="0" max="100" value="{{ old('volume', $gameSettings['volume'] ?? 50) }}">
            </div>
            @php($sounds = [0 => '— без звука —', 1 => 'Уведомление №1', 2 => 'Уведомление №2', 3 => 'Уведомление №3', 4 => 'Уведомление №4', 5 => 'Уведомление №5', 6 => 'Уведомление №6'])
            @foreach(['sound_mention' => 'Обращение ко мне', 'sound_private' => 'Приват', 'sound_clan' => 'Клан', 'sound_trade' => 'Торговля'] as $name => $label)
                @include('interface::partials.settings-select', ['name' => $name, 'label' => $label, 'options' => $sounds, 'selected' => old($name, $gameSettings[$name] ?? 0), 'soundPreview' => true])
            @endforeach
            <input type="hidden" name="mute_open_chat" value="0">
            @include('interface::partials.settings-checkbox', ['name' => 'mute_open_chat', 'label' => 'Без звука при открытом чате', 'checked' => (bool) old('mute_open_chat', $gameSettings['mute_open_chat'] ?? false)])
        </div>
    </section>
    <section class="game-settings-section">
        <h2>Звуки событий</h2>
        <div class="game-settings-grid">
            @foreach([
                ['name' => 'sound_battle', 'label' => 'Начало боя'],
                ['name' => 'sound_dungeon', 'label' => 'Вход в подземелье'],
                ['name' => 'sound_mail', 'label' => 'Новая почта'],
                ['name' => 'sound_gathering', 'label' => 'Сбор ресурса'],
            ] as $setting)
                @include('interface::partials.settings-select', ['name' => $setting['name'], 'label' => $setting['label'], 'options' => $sounds, 'selected' => old($setting['name'], $gameSettings[$setting['name']] ?? 0), 'soundPreview' => true])
            @endforeach
        </div>
    </section>
    <section class="game-settings-section">
        <h2>Безопасность аккаунта</h2>
        <div class="game-settings-security" data-setting-hint="Открывает окно смены пароля. Для сохранения потребуется текущий пароль и подтверждение нового.">
            <span class="butt1 pointer"><span><input type="button" value="Изменить пароль" id="change-password-open"></span></span>
        </div>
    </section>
    <section class="game-settings-section">
        <h2>Интерфейс</h2>
        <div class="game-settings-grid">
            <input type="hidden" name="show_hotbar" value="0">
            @include('interface::partials.settings-checkbox', ['name' => 'show_hotbar', 'label' => 'Показывать панель быстрого доступа', 'checked' => (bool) old('show_hotbar', $gameSettings['show_hotbar'] ?? true)])
            <input type="hidden" name="show_map" value="0">
            @include('interface::partials.settings-checkbox', ['name' => 'show_map', 'label' => 'Показывать карту', 'checked' => (bool) old('show_map', $gameSettings['show_map'] ?? true)])
            <input type="hidden" name="show_item_tooltips" value="0">
            @include('interface::partials.settings-checkbox', ['name' => 'show_item_tooltips', 'label' => 'Подсказки предметов', 'checked' => (bool) old('show_item_tooltips', $gameSettings['show_item_tooltips'] ?? true)])
        </div>
    </section>
    <div class="game-settings-actions">
        <span class="butt1 pointer"><span><input type="submit" value="Сохранить изменения"></span></span>
        <p class="profile-preview-note">Все настройки сохраняются. Громкость применяется ко всем звукам игры; 0 — без звука.</p>
    </div>
</form>
