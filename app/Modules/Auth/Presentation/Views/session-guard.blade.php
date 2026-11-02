<script src="{{ asset('js/game-session.js') }}?v={{ filemtime(public_path('js/game-session.js')) }}"
        data-home-url="{{ route('index') }}"
        data-session-status-url="{{ route('auth.session.status') }}"
        @if($checkSession ?? false) data-check-session="1" @endif></script>
