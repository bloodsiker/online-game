<dialog class="prelaunch-modal" id="prelaunch-modal" aria-labelledby="prelaunch-title"
        data-open-on-load="{{ request()->boolean('launch') || session()->has('launch_subscription_message') ? '1' : '0' }}">
    <section class="prelaunch-modal__content">
        <button class="prelaunch-modal__close" type="button" data-prelaunch-close aria-label="{{ __('prelaunch.close') }}">×</button>
        <div class="prelaunch-modal__ornament" aria-hidden="true">✦</div>
        <h2 id="prelaunch-title">{{ __('prelaunch.title') }}</h2>
        <p>{{ __('prelaunch.intro') }}</p>
        <p class="prelaunch-modal__availability">{{ __('prelaunch.availability') }}</p>
        <a class="prelaunch-modal__signup-link" href="#prelaunch-signup" data-prelaunch-signup>{{ __('prelaunch.signup_link') }}</a>
        <form id="prelaunch-signup" class="prelaunch-modal__form" action="{{ route('homepage.launch-subscription') }}" method="post"
              @if(!session()->has('launch_subscription_message')) hidden @endif
              data-sending="{{ __('prelaunch.sending') }}" data-error="{{ __('prelaunch.error') }}"
              data-expired="{{ __('prelaunch.expired') }}" data-rate-limited="{{ __('prelaunch.rate_limited') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <p>{{ __('prelaunch.signup_hint') }}</p>
            <label for="prelaunch-email">{{ __('prelaunch.email') }}</label>
            <input id="prelaunch-email" name="email" type="email" maxlength="254" required autocomplete="email" value="{{ old('email') }}">
            <button class="prelaunch-modal__submit" type="submit">{{ __('prelaunch.subscribe') }}</button>
        </form>
        <p class="prelaunch-modal__result" role="status" aria-live="polite" @if(!session()->has('launch_subscription_message')) hidden @endif>{{ session('launch_subscription_message') }}</p>
    </section>
</dialog>
<script src="{{ asset('js/prelaunch.js') }}?v={{ filemtime(public_path('js/prelaunch.js')) }}" defer></script>
