<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tab === 'profile' ? 'Анкета' : 'Настройки игры' }}</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}?v={{ filemtime(public_path('css/main.css')) }}">
    <style>
        html { min-height: 100%; }
        body { margin: 0; padding: 10px; color: #461c0b; font: 11px Tahoma, Arial, sans-serif; background: url({{ asset('img/bg/bgg.gif') }}) repeat; }
        .settings-frame { min-height: calc(100vh - 46px); padding: 12px; border: 1px solid #b79a76; background: url({{ asset('img/bg/bgg.gif') }}) repeat; }
        .settings-tabs { border-collapse: collapse; margin-bottom: 12px; }
        .settings-tabs img { display: block; }
        .settings-tab { padding: 0 2px 4px; background-position: center top; background-repeat: repeat-x; text-align: center; white-space: nowrap; }
        .settings-tab a { color: #461c0b; font-weight: bold; text-decoration: none; }
        .settings-tab.active a { color: #ffe9ba; }
        .settings-placeholder { padding: 30px 15px; border: 1px solid #cfb89b; background: rgba(255, 248, 234, .6); text-align: center; }
        .settings-placeholder h1 { margin: 0 0 10px; font-size: 15px; }
        .settings-placeholder p { margin: 0; color: #796653; }
        .profile-form { padding: 8px 6px; }
        .profile-table { width: 50%; min-width: 530px; border-collapse: collapse; color: #795b48; }
        .profile-table th { width: 130px; padding: 2px 0; font-weight: normal; text-align: left; vertical-align: middle; }
        .profile-table td { padding: 2px 0; }
        .profile-input-wrap { display: inline-block; width: 230px; height: 23px; vertical-align: middle; background: url({{ asset('main/images/gui/input.png') }}) no-repeat 0 0; }
        .profile-input-inner { height: 100%; margin-left: 6px; padding-right: 6px; background: url({{ asset('main/images/gui/input.png') }}) no-repeat 100% -23px; }
        .profile-input-inner input { box-sizing: border-box; width: 100%; height: 23px; padding: 0 4px; border: 0; outline: none; color: #6c382c; background: transparent; font: 11px Tahoma, Arial, sans-serif; }
        .profile-input-wrap:focus-within { background-position: 0 -92px; }
        .profile-input-wrap:focus-within .profile-input-inner { background-position: 100% -115px; }
        .profile-birthday { display: flex; flex-wrap: wrap; gap: 3px; }
        .profile-select-wrap { display: inline-block; position: relative; vertical-align: middle; }
        .profile-select-wrap::after { content: ''; position: absolute; right: 2px; top: 0; width: 23px; height: 23px; background: url({{ asset('main/images/gui/btn-select.png') }}) no-repeat 0 0; pointer-events: none; }
        .profile-select { box-sizing: border-box; height: 23px; padding: 2px 27px 2px 6px; border: 1px solid #baa276; border-radius: 4px; color: #6c382c; background: #f9e4ab; box-shadow: inset 0 1px 3px rgba(106, 74, 33, .2); appearance: none; font: 11px Tahoma, Arial, sans-serif; cursor: pointer; }
        .profile-select:focus-visible { outline: 1px solid #9c6c3b; }
        .profile-select option { background: #fff3cf; }
        .profile-select-trigger { text-align: left; }
        .profile-select-trigger:focus-visible { outline: 1px solid #9c6c3b; }
        .profile-select-wrap.is-open { z-index: 10; }
        .profile-select-wrap.is-open::after { background-position: -46px 0; }
        .profile-select-list { position: absolute; z-index: 20; top: calc(100% - 1px); left: 0; box-sizing: border-box; min-width: 100%; max-height: 180px; overflow-y: auto; padding: 3px; border: 1px solid #ad895c; border-radius: 3px; background: #f9e4ab url({{ asset('img/bg/bgg.gif') }}) repeat; box-shadow: inset 0 0 0 1px #fff1c9, 0 3px 6px rgba(70, 28, 11, .3); scrollbar-width: thin; scrollbar-color: #b89b70 #f9e4ab; }
        .profile-select-option { display: block; padding: 4px 7px; border-radius: 2px; white-space: nowrap; color: #6c382c; cursor: pointer; font: 11px Tahoma, Arial, sans-serif; }
        .profile-select-option[aria-selected="true"] { font-weight: bold; background: rgba(195, 153, 91, .25); }
        .profile-select-option.is-highlighted { color: #fff2ce; background: linear-gradient(#a65a3b, #76351e); box-shadow: inset 0 1px rgba(255, 238, 197, .35); }
        .profile-select-list[hidden], .profile-select[hidden] { display: none; }
        .profile-textarea { box-sizing: border-box; display: block; width: 100%; min-height: 175px; padding: 6px; resize: vertical; border: 1px solid #baa276; border-radius: 4px; outline-color: #9c6c3b; color: #6c382c; background: #f9e4ab; box-shadow: inset 0 1px 4px rgba(106, 74, 33, .22); font: 11px Tahoma, Arial, sans-serif; }
        .profile-actions { padding-top: 10px !important; text-align: center; }
        .profile-actions .butt1 input { width: 170px; }
        .profile-preview-note { margin: 8px 0 0; font-size: 10px; color: #927660; }
        .game-settings { width: 750px; max-width: 100%; padding: 8px 6px; box-sizing: border-box; color: #795b48; }
        .game-settings-section { margin-bottom: 8px; }
        .game-settings-section h2 { margin: 0 0 7px; padding: 0 10px 3px; border-bottom: 1px solid #cfb899; color: #a4150c; font-size: 11px; }
        .game-settings-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px 20px; padding: 0 10px; }
        .game-settings-control { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 24px; }
        .game-settings-control label { flex: 1; cursor: pointer; }
        .settings-sound-preview { flex: 0 0 23px; width: 23px; height: 23px; padding: 0; border: 1px solid #baa276; border-radius: 4px; background: #f9e4ab; color: #6c382c; font-size: 17px; cursor: pointer; }
        .settings-sound-preview:disabled { opacity: .45; cursor: default; }
        .game-settings-tooltip { position: fixed; z-index: 1010; width: 350px; max-width: calc(100vw - 20px); box-sizing: border-box; padding: 6px 8px 7px; border: 1px solid #e3b360; background: #f8e5a8; color: #955c4a; font: 11px/1.4 Tahoma, Arial, sans-serif; box-shadow: 0 3px 3px 1px rgba(41,13,5,.4); pointer-events: none; overflow-wrap: anywhere; }
        .game-settings-tooltip[hidden] { display: none; }
        .game-settings-checkbox { flex: 0 0 24px; width: 24px; height: 23px; margin: 0; border: 0; background: url({{ asset('main/images/gui/gui-checkbox.png') }}) no-repeat 0 -23px; appearance: none; cursor: pointer; }
        .game-settings-checkbox:checked { background-position: 0 0; }
        .game-settings-checkbox:focus-visible { outline: 1px solid #9c6c3b; border-radius: 4px; }
        .game-settings-volume { --volume-fill: 50%; width: 220px; max-width: 65%; height: 24px; margin: 0; padding: 0; appearance: none; -webkit-appearance: none; border: 0; background: transparent; cursor: pointer; }
        .game-settings-volume::-webkit-slider-runnable-track { height: 11px; box-sizing: border-box; border: 1px solid #c5c5c5; border-radius: 3px; background: linear-gradient(to right, #ffe88f 0 var(--volume-fill), #fff var(--volume-fill) 100%); }
        .game-settings-volume::-webkit-slider-thumb { width: 15px; height: 15px; margin-top: -3px; appearance: none; -webkit-appearance: none; border: 1px solid #db9f73; border-radius: 3px; background: #fff1c5; }
        .game-settings-volume::-moz-range-track { height: 9px; border: 1px solid #c5c5c5; border-radius: 3px; background: #fff; }
        .game-settings-volume::-moz-range-progress { height: 9px; border-radius: 3px; background: #ffe88f; }
        .game-settings-volume::-moz-range-thumb { width: 13px; height: 13px; border: 1px solid #db9f73; border-radius: 3px; background: #fff1c5; }
        .game-settings-volume:focus-visible { outline: 1px solid #9c6c3b; outline-offset: 2px; border-radius: 3px; }
        .game-settings-actions { text-align: center; padding-top: 8px; }
        .game-settings-actions .butt1 input { width: 170px; }
        .game-settings-security { text-align: center; }
        .game-settings-security .butt1 input { width: 120px; }
        .password-overlay { position: fixed; inset: 0; z-index: 1002; background: rgba(0, 0, 0, .45); }
        .password-modal { position: fixed; z-index: 1003; left: 50%; top: 50%; transform: translate(-50%, -50%); max-height: calc(100vh - 24px); overflow-y: auto; width: 420px; max-width: calc(100vw - 24px); }
        .password-modal .popup_global_container { width: 100%; }
        .password-modal .popup_global_content { padding: 12px 18px 6px; }
        .password-field { margin-bottom: 9px; }
        .password-field label { display: block; margin-bottom: 4px; }
        .password-field-error { margin-top: 4px; color: #a4150c; font-size: 11px; }
        .password-field:has([aria-invalid="true"]) .profile-input-wrap { outline: 1px solid #a4150c; border-radius: 4px; }
        .password-field .profile-input-wrap { width: 100%; }
        .password-actions { display: flex; justify-content: center; gap: 6px; margin-top: 12px; }
        .password-actions .butt1 input { width: 65px; }
        .password-message { margin-top: 8px; padding: 6px 8px; border: 1px solid #cba66d; background: #feefb6; color: #8b2000; }
        .password-message.is-success { color: #31551e; }
        .password-overlay[hidden], .password-modal[hidden], .password-message[hidden] { display: none; }
        @media (max-width: 600px) { .game-settings-grid { grid-template-columns: 1fr; } }
        @media (max-width: 600px) { .profile-table { width: 100%; min-width: 0; } .profile-table th { width: 110px; } .profile-input-wrap { width: min(230px, 100%); } }
    </style>
</head>
<body>
    <section class="settings-frame">
        <table class="settings-tabs" cellpadding="0" cellspacing="0" aria-label="Разделы настроек">
            <tbody><tr>
                @foreach([
                    ['id' => 'game', 'route' => 'settings', 'label' => 'Настройки игры'],
                    ['id' => 'profile', 'route' => 'settings.profile', 'label' => 'Анкета'],
                ] as $item)
                    @php($active = $tab === $item['id'])
                    @php($suffix = $active ? '2' : '1')
                    <td><img src="{{ asset('img/bg/btn/btn-left'.$suffix.'.gif') }}" width="19" height="21" alt=""></td>
                    <td class="settings-tab{{ $active ? ' active' : '' }}" style="background-image: url({{ asset('img/bg/btn/btn-cent'.$suffix.'.gif') }});">
                        <a href="{{ route($item['route']) }}" @if($active) aria-current="page" @endif>{{ $item['label'] }}</a>
                    </td>
                    <td><img src="{{ asset('img/bg/btn/btn-right'.$suffix.'.gif') }}" width="19" height="21" alt=""></td>
                @endforeach
            </tr></tbody>
        </table>
        @if($tab === 'profile')
            @include('interface::partials.profile-form')
        @else
            @include('interface::partials.game-settings-form')
        @endif
    </section>
    @if($tab === 'game')
        @include('interface::partials.change-password-modal')
        <script src="{{ asset('js/change-password-modal.js') }}?v={{ filemtime(public_path('js/change-password-modal.js')) }}" defer></script>
        <script src="{{ asset('js/settings-tooltips.js') }}?v={{ filemtime(public_path('js/settings-tooltips.js')) }}" defer></script>
        <script src="{{ asset('js/notification-sounds.js') }}?v={{ filemtime(public_path('js/notification-sounds.js')) }}" data-sound-base="{{ asset('audio/notifications') }}/" defer></script>
    @endif
    <script src="{{ asset('js/profile-select.js') }}?v={{ filemtime(public_path('js/profile-select.js')) }}" defer></script>
    @if($tab === 'game')
        <script>
            (() => {
                const volume = document.getElementById('setting-volume');
                if (!volume) return;
                const updateVolumeFill = () => {
                    const percent = (Number(volume.value) - Number(volume.min)) / (Number(volume.max) - Number(volume.min)) * 100;
                    volume.style.setProperty('--volume-fill', percent + '%');
                };
                volume.addEventListener('input', updateVolumeFill);
                volume.addEventListener('change', updateVolumeFill);
                updateVolumeFill();
            })();
        </script>
    @endif
    @if(session('reload_game_after_settings'))
        <script>
            if (window.top !== window) {
                window.top.location.reload();
            } else {
                window.location.replace(@js(route('game')));
            }
        </script>
    @endif
    @if(session('message') && !session('reload_game_after_settings'))
        <script>
            window.parent.showErrorIframe?.(@js(session('message')));
        </script>
    @endif
</body>
</html>
