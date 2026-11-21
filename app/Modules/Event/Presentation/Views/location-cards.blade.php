@if($locationEvents !== [])
    <div class="event-beacon" data-event-beacon>
        <button type="button" class="event-beacon__button" aria-expanded="false" aria-controls="event-beacon-panel">
            <span class="event-beacon__ribbon">
                <span class="event-beacon__flag" aria-hidden="true">⚑</span>
                <span class="event-beacon__label">{{ __('local-events.label') }}</span>
                <span class="event-beacon__count" data-local-events-count>{{ count($locationEvents) }}</span>
            </span>
        </button>

        <section class="event-beacon__panel event-tip local-events" id="event-beacon-panel" aria-label="{{ __('local-events.heading') }}">
            {{-- Оформление как у подсказки предмета (#artifact_alt), только шире --}}
            <table class="event-tip__table" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td class="event-tip__tl"></td>
                    <td class="event-tip__t"><b>{{ __('local-events.heading') }}</b></td>
                    <td class="event-tip__tr"></td>
                </tr>
                <tr>
                    <td class="event-tip__l"></td>
                    <td class="event-tip__body">
                        @foreach($locationEvents as $event)
                            <div class="local-event {{ $loop->even ? 'event-tip__row--dark' : '' }}" data-local-event-end="{{ $event['ends_at'] }}">
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
                    </td>
                    <td class="event-tip__r"></td>
                </tr>
                <tr>
                    <td class="event-tip__bl"></td>
                    <td class="event-tip__b"></td>
                    <td class="event-tip__br"></td>
                </tr>
                </tbody>
            </table>
        </section>
    </div>
@endif
