@php
    $isExisting = $stage !== null;
    $useOld = old('stage_id') !== null
        ? $isExisting && (int) old('stage_id') === $stage->id
        : ! $isExisting && old('name') !== null;
    $stageMonsters = $useOld
        ? collect(old('monsters', []))
        : ($isExisting
            ? $stage->monsters->map(fn ($definition) => [
                'monster_id' => $definition->monster_id,
                'location_id' => $definition->location_id,
                'quantity' => $definition->quantity,
                'weight' => $definition->weight,
            ])
            : collect([['monster_id' => '', 'location_id' => '', 'quantity' => 30, 'weight' => 1]]));
    $stageMonsters = $stageMonsters->isNotEmpty()
        ? $stageMonsters
        : collect([['monster_id' => '', 'location_id' => '', 'quantity' => 1, 'weight' => 1]]);
    $selectedLocationIds = $useOld
        ? array_map('intval', old('location_ids', []))
        : ($isExisting ? $stage->locations->modelKeys() : []);
    $entryLocationId = $useOld
        ? (int) old('entry_location_id')
        : ($isExisting ? $stage->entryLocation()?->id : null);
    $spawnType = $useOld
        ? old('spawn_type')
        : ($isExisting ? $stage->spawn_type->value : \App\Modules\Dungeon\Domain\Enums\DungeonStageSpawnType::DISTRIBUTED->value);
@endphp

<form action="{{ route('admin.dungeon.stage.save', $dungeon) }}" method="post" class="p-3 border rounded {{ $isExisting ? 'mb-2' : '' }}" data-stage-form>
    @csrf
    @if($isExisting)<input type="hidden" name="stage_id" value="{{ $stage->id }}">@endif

    @unless($isExisting)<h4>Добавить этаж</h4>@endunless
    <div class="row">
        <div class="col-md-1"><label>№</label><input class="form-control" type="number" name="number" value="{{ $useOld ? old('number') : ($isExisting ? $stage->number : $dungeon->stages->max('number') + 1) }}" min="1" required></div>
        <div class="col-md-3"><label>Название</label><input class="form-control" name="name" value="{{ $useOld ? old('name') : ($stage?->name ?? '') }}" required></div>
        <div class="col-md-2"><label>Время, сек.</label><input class="form-control" type="number" name="time_limit_seconds" value="{{ $useOld ? old('time_limit_seconds') : ($stage?->time_limit_seconds ?? 180) }}" min="10"></div>
        <div class="col-md-3"><label>Размещение</label><select class="form-control stage-spawn-type" name="spawn_type">@foreach(\App\Modules\Dungeon\Domain\Enums\DungeonStageSpawnType::cases() as $type)<option value="{{ $type->value }}" @selected($spawnType === $type->value)>{{ $type->label() }}</option>@endforeach</select></div>
        <div class="col-md-2 stage-total-monsters"><label>Всего монстров</label><input class="form-control" type="number" name="total_monsters" value="{{ $useOld ? old('total_monsters') : ($stage?->total_monsters ?? 30) }}" min="1" max="1000" required></div>
    </div>

    <div class="row mt-3">
        <div class="col-md-7"><label>Локации этажа</label><select class="form-control" name="location_ids[]" multiple size="6" required>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(in_array($location->id, $selectedLocationIds, true))>[{{ $location->id }}] {{ $location->name }}</option>@endforeach</select></div>
        <div class="col-md-5"><label>Вход на этаж</label><select class="form-control" name="entry_location_id" required>@foreach($locations as $location)<option value="{{ $location->id }}" @selected($entryLocationId === $location->id)>[{{ $location->id }}] {{ $location->name }}</option>@endforeach</select></div>
    </div>

    <div class="mt-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <strong>Монстры этажа</strong>
                <span class="help-block stage-monster-help mb-none"></span>
            </div>
            <button type="button" class="btn btn-info btn-sm stage-monster-add">+ Добавить монстра</button>
        </div>
        <div class="stage-monster-list">
            @foreach($stageMonsters as $index => $definition)
                <div class="row stage-monster-row mb-2">
                    <div class="col-md-5">
                        <label>Монстр</label>
                        <select class="form-control" name="monsters[{{ $index }}][monster_id]" required>
                            <option value="">— Выберите —</option>
                            @foreach($monsters as $monster)<option value="{{ $monster->id }}" @selected((int) ($definition['monster_id'] ?? 0) === $monster->id)>[{{ $monster->id }}] {{ $monster->name }} ({{ $monster->lvl }} ур.)</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2 stage-monster-quantity"><label>Количество</label><input class="form-control" type="number" name="monsters[{{ $index }}][quantity]" value="{{ $definition['quantity'] ?? 1 }}" min="1" max="1000" required></div>
                    <div class="col-md-2 stage-monster-weight"><label>Вес</label><input class="form-control" type="number" name="monsters[{{ $index }}][weight]" value="{{ $definition['weight'] ?? 1 }}" min="1" max="1000" required></div>
                    <div class="col-md-2 stage-monster-location"><label>Локация</label><select class="form-control" name="monsters[{{ $index }}][location_id]"><option value="">Вход этажа</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected((int) ($definition['location_id'] ?? 0) === $location->id)>[{{ $location->id }}] {{ $location->name }}</option>@endforeach</select></div>
                    <div class="col-md-1"><label>&nbsp;</label><button type="button" class="btn btn-danger btn-block stage-monster-remove" title="Удалить">×</button></div>
                </div>
            @endforeach
        </div>
        @if($useOld)
            @error('monsters')<div class="text-danger">{{ $message }}</div>@enderror
            @foreach($errors->get('monsters.*') as $messages)
                @foreach($messages as $message)<div class="text-danger">{{ $message }}</div>@endforeach
            @endforeach
        @endif
    </div>

    <template class="stage-monster-template">
        <div class="row stage-monster-row mb-2">
            <div class="col-md-5"><label>Монстр</label><select class="form-control" name="monsters[__INDEX__][monster_id]" required><option value="">— Выберите —</option>@foreach($monsters as $monster)<option value="{{ $monster->id }}">[{{ $monster->id }}] {{ $monster->name }} ({{ $monster->lvl }} ур.)</option>@endforeach</select></div>
            <div class="col-md-2 stage-monster-quantity"><label>Количество</label><input class="form-control" type="number" name="monsters[__INDEX__][quantity]" value="1" min="1" max="1000" required></div>
            <div class="col-md-2 stage-monster-weight"><label>Вес</label><input class="form-control" type="number" name="monsters[__INDEX__][weight]" value="1" min="1" max="1000" required></div>
            <div class="col-md-2 stage-monster-location"><label>Локация</label><select class="form-control" name="monsters[__INDEX__][location_id]"><option value="">Вход этажа</option>@foreach($locations as $location)<option value="{{ $location->id }}">[{{ $location->id }}] {{ $location->name }}</option>@endforeach</select></div>
            <div class="col-md-1"><label>&nbsp;</label><button type="button" class="btn btn-danger btn-block stage-monster-remove" title="Удалить">×</button></div>
        </div>
    </template>

    <div class="mt-3"><button class="btn {{ $isExisting ? 'btn-primary' : 'btn-success' }}">{{ $isExisting ? 'Сохранить этаж' : 'Добавить этаж' }}</button></div>
</form>

@if($isExisting)
    <form action="{{ route('admin.dungeon.stage.delete', [$dungeon, $stage]) }}" method="post" class="mb-4 mt-2" onsubmit="return confirm('Удалить этаж?')">
        @csrf @method('DELETE')
        <button class="btn btn-danger btn-sm">Удалить этаж {{ $stage->number }}</button>
    </form>
@endif
