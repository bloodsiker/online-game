@php
    $weatherService = app(\App\Modules\Weather\Application\Services\WeatherService::class);
    $weatherRow = $weatherService->currentForLocation((int) $locationId);
    $weatherMoveMs = $weatherService->moveCooldownMs((int) $locationId);
@endphp
<script>try { parent.moveCooldownDuration = @js($weatherMoveMs); } catch (e) {}</script>
@if($weatherRow !== null && $weatherRow->weather !== null)
    @php
        $weatherType = $weatherRow->weather;
        $weatherMessage = $weatherType->description().' '.$weatherType->effectText();
    @endphp
    <a href="#"
       class="weather-label"
       style="color: {{ $weatherType->isGood() ? '#1f7a1f' : '#b80000' }}; font-weight: bold; text-decoration: none; cursor: pointer;"
       title="{{ __('Погода') }}: {{ $weatherType->label() }}"
       onclick="parent.openGameMessageModal({ title: @js(__('Погода').': '.$weatherType->label()), message: @js($weatherMessage) }); return false;">({{ $weatherType->label() }})</a>
@endif
