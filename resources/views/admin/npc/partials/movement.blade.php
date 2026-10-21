<section class="card mt-3">
    <header class="card-header">
        <h2 class="card-title">Автоматическое перемещение</h2>
    </header>
    <div class="card-body">
        <form action="{{ route('admin.npc.movement', $npc->id) }}" method="post" id="npc-movement-form">
            @csrf
            <div class="checkbox-custom checkbox-default">
                <input type="checkbox" id="movement-enabled" name="enabled" value="1" @checked(old('enabled', $npc->movement?->enabled ?? false))>
                <label for="movement-enabled">NPC самостоятельно меняет локацию</label>
            </div>
            <div class="form-group">
                <label for="movement-mode">Где перемещается</label>
                <select id="movement-mode" name="mode" class="form-control">
                    <option value="map" @selected(old('mode', $npc->movement?->mode) !== 'locations')>По карте</option>
                    <option value="locations" @selected(old('mode', $npc->movement?->mode) === 'locations')>По списку локаций</option>
                </select>
            </div>
            <div class="form-group" id="movement-map-field">
                <label for="movement-map">Карта</label>
                <select id="movement-map" name="map_id" class="form-control">
                    <option value=""></option>
                    @if($movementMap)
                        <option value="{{ $movementMap->id }}" selected>[{{ $movementMap->id }}] {{ $movementMap->name }}</option>
                    @endif
                </select>
            </div>
            <div class="form-group" id="movement-locations-field">
                <label for="movement-locations">Разрешённые локации</label>
                <select id="movement-locations" name="location_ids[]" multiple class="form-control">
                    @foreach($movementLocations as $location)
                        <option value="{{ $location->id }}" selected>[{{ $location->id }}] {{ $location->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="movement-interval">Интервал перемещения, минут</label>
                <input type="number" id="movement-interval" name="interval_minutes" class="form-control" min="1" max="10080"
                       value="{{ old('interval_minutes', $npc->movement?->interval_minutes ?? 10) }}" required>
            </div>
            <p class="text-muted">За один шаг NPC выбирает случайную соседнюю локацию по существующим переходам (север, юг, восток, запад, вверх, вниз), только в пределах выбранной карты или списка. Порядок списка не учитывается. Закрытые локации и данжи исключены. Если доступных соседей нет, NPC остаётся на месте. Разместите NPC в связанной части маршрута. Неактивные NPC не перемещаются. Проверка — раз в минуту.</p>
            @if($npc->movement?->enabled && $npc->movement?->next_move_at)
                <p>Следующая проверка перемещения: {{ $npc->movement->next_move_at->format('d.m.Y H:i') }}</p>
            @endif
            <button type="submit" class="btn btn-primary">Сохранить перемещение</button>
        </form>
    </div>
</section>

@push('footer_scripts')
    <script>
        (function () {
            function initMovementSelect(selector, url, placeholder) {
                $(selector).select2({
                    theme: 'bootstrap',
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    ajax: {
                        url: url,
                        dataType: 'json',
                        delay: 250,
                        data: function (params) { return {q: params.term, page: params.page || 1}; },
                        processResults: function (data) { return data; }
                    }
                });
            }

            initMovementSelect('#movement-map', @json(route('admin.api.maps')), 'Выберите карту');
            initMovementSelect('#movement-locations', @json(route('admin.api.locations')), 'Выберите минимум две локации');

            function updateMovementMode() {
                const byMap = document.getElementById('movement-mode').value === 'map';
                document.getElementById('movement-map-field').hidden = !byMap;
                document.getElementById('movement-locations-field').hidden = byMap;
                document.getElementById('movement-map').disabled = !byMap;
                document.getElementById('movement-locations').disabled = byMap;
            }

            document.getElementById('movement-mode').addEventListener('change', updateMovementMode);
            updateMovementMode();
        })();
    </script>
@endpush
