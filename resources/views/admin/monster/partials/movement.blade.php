<div class="pt-3">
    <form action="{{ route('admin.monster.movement', $monster->id) }}" method="post" id="monster-movement-form">
        @csrf
        <div class="checkbox-custom checkbox-default">
            <input type="checkbox" id="boss-movement-enabled" name="enabled" value="1" @checked(old('enabled', $monster->movement?->enabled ?? false))>
            <label for="boss-movement-enabled">Босс самостоятельно перемещается по карте</label>
        </div>
        <div class="form-group">
            <label for="boss-movement-mode">Маршрут</label>
            <select id="boss-movement-mode" name="mode" class="form-control">
                <option value="map" @selected(old('mode', $monster->movement?->mode) !== 'locations')>По карте</option>
                <option value="locations" @selected(old('mode', $monster->movement?->mode) === 'locations')>По списку локаций</option>
            </select>
        </div>
        <div class="form-group" id="boss-movement-map-field">
            <label for="boss-movement-map">Карта</label>
            <select id="boss-movement-map" name="map_id" class="form-control">
                <option value=""></option>
                @if($movementMap)
                    <option value="{{ $movementMap->id }}" selected>[{{ $movementMap->id }}] {{ $movementMap->name }}</option>
                @endif
            </select>
        </div>
        <div class="form-group" id="boss-movement-locations-field">
            <label for="boss-movement-locations">Разрешённые локации</label>
            <select id="boss-movement-locations" name="location_ids[]" multiple class="form-control">
                @foreach($movementLocations as $location)
                    <option value="{{ $location->id }}" selected>[{{ $location->id }}] {{ $location->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="boss-movement-interval">Интервал, минут</label>
            <input type="number" id="boss-movement-interval" name="interval_minutes" class="form-control" min="1" max="10080"
                   value="{{ old('interval_minutes', $monster->movement?->interval_minutes ?? 10) }}" required>
        </div>
        <p class="text-muted">На каждом шаге босс выбирает случайную соседнюю локацию по существующему переходу в пределах маршрута. Закрытые локации и данжи исключены. Босс не перемещается во время боя, а также в событиях и инстансах. Если соседних локаций маршрута нет, он останется на месте. Проверка выполняется раз в минуту.</p>
        @if($monster->movement?->enabled && $monster->movement?->next_move_at)
            <p>Следующее перемещение: {{ $monster->movement->next_move_at->format('d.m.Y H:i') }}</p>
        @endif
        <button type="submit" class="btn btn-primary">Сохранить перемещение</button>
    </form>
</div>

@push('footer_scripts')
    <script>
        (function () {
            $('#boss-movement-map').select2({
                theme: 'bootstrap', width: '100%', placeholder: 'Выберите карту', allowClear: true,
                ajax: {
                    url: @json(route('admin.api.maps')), dataType: 'json', delay: 250,
                    data: function (params) { return {q: params.term, page: params.page || 1}; },
                    processResults: function (data) { return data; }
                }
            });
            $('#boss-movement-locations').select2({
                theme: 'bootstrap', width: '100%', placeholder: 'Выберите минимум две локации', allowClear: true,
                ajax: {
                    url: @json(route('admin.api.locations')), dataType: 'json', delay: 250,
                    data: function (params) { return {q: params.term, page: params.page || 1}; },
                    processResults: function (data) { return data; }
                }
            });

            const mode = document.getElementById('boss-movement-mode');
            function updateMode() {
                const byMap = mode.value === 'map';
                document.getElementById('boss-movement-map-field').hidden = !byMap;
                document.getElementById('boss-movement-locations-field').hidden = byMap;
                document.getElementById('boss-movement-map').disabled = !byMap;
                document.getElementById('boss-movement-locations').disabled = byMap;
            }
            mode.addEventListener('change', updateMode);
            updateMode();
        })();
    </script>
@endpush
