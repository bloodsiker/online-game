@php($prefix = $trap ? 'trap-'.$trap->id : 'trap-new')
@include('admin.partials.content-translations', ['prefix' => $prefix, 'content' => $trap, 'fields' => ['name' => ['label' => 'Название (показывается в логе смерти)']]])

<div class="form-group">
    <label class="col-form-label">Локация (где стоит ловушка)</label>
    <select class="trap-location-select form-control" name="location_id" required>
        @if($trap?->location)
            <option value="{{ $trap->location->id }}" selected>[{{ $trap->location->id }}] {{ $trap->location->name }}</option>
        @endif
    </select>
</div>

<div class="form-group">
    <label class="col-form-label">Срабатывает при выходе</label>
    <select class="form-control" name="direction">
        <option value="">В любую сторону</option>
        @foreach($directions as $direction)
            <option value="{{ $direction }}" @selected(($trap->direction ?? '') === $direction)>{{ ['north' => 'На север', 'south' => 'На юг', 'east' => 'На восток', 'west' => 'На запад'][$direction] }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label class="col-form-label">Тип ловушки</label>
    <select class="form-control" name="type" id="trap-type">
        @foreach($types as $key => $type)
            <option value="{{ $key }}" @selected(($trap->type ?? array_key_first($types)) === $key)>{{ $type->label() }}</option>
        @endforeach
    </select>
</div>

@foreach($types as $key => $type)
    <div class="trap-config" data-type="{{ $key }}">
        @foreach($type->configFields() as $field)
            @php($value = $trap && $trap->type === $key ? ($trap->config[$field['key']] ?? $field['default']) : $field['default'])
            <div class="form-group">
                <label class="col-form-label">{{ $field['label'] }}</label>
                @if($field['kind'] === 'effect')
                    <select class="form-control" name="config[{{ $field['key'] }}]" data-type-field>
                        <option value="">— без эффекта —</option>
                        @foreach($effects as $effect)
                            <option value="{{ $effect->slug }}" @selected((string) $value === $effect->slug)>{{ $effect->name }} ({{ $effect->slug }})</option>
                        @endforeach
                    </select>
                @else
                    <input type="number" step="{{ $field['step'] ?? 1 }}" min="0" class="form-control" name="config[{{ $field['key'] }}]" value="{{ $value }}" data-type-field>
                @endif
                @isset($field['hint'])<small class="text-muted">{{ $field['hint'] }}</small>@endisset
            </div>
        @endforeach
    </div>
@endforeach

<div class="form-group form-check">
    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked($trap?->is_active ?? true)>
    <label class="form-check-label" for="is_active">Активна</label>
</div>
