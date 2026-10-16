<div class="game-settings-control" @if(!empty($settingHints[$name])) data-setting-hint="{{ $settingHints[$name] }}" @endif>
    <label for="setting-{{ $name }}">{{ $label }}</label>
    <span class="profile-select-wrap">
        <select id="setting-{{ $name }}" name="{{ $name }}" class="profile-select">
            @foreach($options as $value => $text)
                <option value="{{ $value }}" @selected((string) ($selected ?? array_key_first($options)) === (string) $value)>{{ $text }}</option>
            @endforeach
        </select>
    </span>
    @if($soundPreview ?? false)
        <button type="button" class="settings-sound-preview" data-sound-preview="{{ $name }}" title="{{ __('ui.preview_sound') }}" aria-label="{{ __('ui.preview_sound_label', ['label' => $label]) }}">&#9835;</button>
    @endif
</div>
