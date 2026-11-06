@if($locationEvents !== [])
    <section class="local-events" aria-label="{{ __('local-events.heading') }}">
        <x-game.inset-frame>
        @foreach($locationEvents as $event)
            <div class="local-event" data-local-event-end="{{ $event['ends_at'] }}">
                <div class="local-event__body">
                    <a class="local-event__title" href="{{ $event['url'] }}">⚑ {{ $event['title'] }}</a>
                    <div>{{ $event['instruction'] }} {{ $event['targets'] }}</div>
                    <div class="local-event__progress">{{ __('local-events.global') }} <b>{{ $event['global'] }}/{{ $event['global_limit'] }}</b>
                        <span> · {{ __('local-events.personal') }} <b>{{ $event['personal'] }}/{{ $event['player_limit'] }}</b></span>
                    </div>
                    @if($event['limit_reached'])<div class="local-event__limit">{{ __('local-events.limit') }}</div>@endif
                </div>
                <div class="local-event__timer">{{ __('local-events.remaining') }}<br><b data-local-event-timer></b></div>
            </div>
        @endforeach
        </x-game.inset-frame>
    </section>
@endif
