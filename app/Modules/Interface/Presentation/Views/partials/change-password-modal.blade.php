<div class="password-overlay" id="password-overlay" hidden></div>
<div class="password-modal" id="password-modal" role="dialog" aria-modal="true" aria-labelledby="password-modal-title" hidden>
    <div class="popup_global_container">
        <div class="popup-top-left">
            <div class="popup-top-right"><div class="popup-top-center">
                <div class="popup_global_title" id="password-modal-title">{{ __('ui.change_password') }}</div>
            </div></div>
            <button class="popup_global_close_btn" id="password-modal-close" type="button" aria-label="{{ __('ui.close') }}" style="border:0;padding:0;"></button>
        </div>
        <div class="popup-left-center"><div class="popup-right-center">
            <div class="popup_global_content">
                <form id="change-password-form" action="{{ route('settings.password.update') }}" method="post"
                      data-translations="{{ json_encode(collect(['password_mismatch', 'saving', 'save', 'session_expired', 'too_many_attempts', 'login_again', 'password_failed', 'password_changed', 'connection_failed'])->mapWithKeys(fn ($key) => [$key => __('ui.'.$key)])->all()) }}">
                    @csrf
                    @foreach([
                        ['name' => 'current_password', 'label' => __('ui.current_password'), 'autocomplete' => 'current-password'],
                        ['name' => 'password', 'label' => __('ui.new_password'), 'autocomplete' => 'new-password'],
                        ['name' => 'password_confirmation', 'label' => __('ui.confirm_password'), 'autocomplete' => 'new-password'],
                    ] as $field)
                        <div class="password-field">
                            <label for="change-{{ $field['name'] }}">{{ $field['label'] }}</label>
                            <div class="profile-input-wrap"><div class="profile-input-inner">
                                <input id="change-{{ $field['name'] }}" type="password" name="{{ $field['name'] }}" autocomplete="{{ $field['autocomplete'] }}" required @if($field['name'] !== 'current_password') minlength="8" maxlength="72" @endif @if($field['name'] === 'password_confirmation') aria-describedby="password-confirmation-error" @endif>
                            </div></div>
                            @if($field['name'] === 'password_confirmation')
                                <div id="password-confirmation-error" class="password-field-error" aria-live="polite" hidden>{{ __('ui.password_mismatch') }}</div>
                            @endif
                        </div>
                    @endforeach
                    <div class="password-message" id="password-message" role="status" aria-live="polite" hidden></div>
                    <div class="password-actions">
                        <span class="butt1 pointer"><span><input type="submit" value="{{ __('ui.save') }}" id="password-save"></span></span>
                        <span class="butt1 pointer"><span><input type="button" value="{{ __('ui.cancel_action') }}" id="password-cancel"></span></span>
                    </div>
                </form>
            </div>
        </div></div>
        <div class="popup-left-bottom"><div class="popup-right-bottom"><div class="popup-bottom-center"></div></div></div>
    </div>
</div>
