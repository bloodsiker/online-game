<div class="game-settings-control" @if(!empty($settingHints[$name])) data-setting-hint="{{ $settingHints[$name] }}" @endif>
    <label for="setting-{{ $name }}">{{ $label }}</label>
    <input class="game-settings-checkbox" id="setting-{{ $name }}" name="{{ $name }}" type="checkbox" value="1" @checked($checked ?? false)>
</div>
