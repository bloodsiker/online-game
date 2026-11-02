@extends('admin.layout.base')
@section('title', 'Настройка карты сокровищ')
@section('body')
<section class="card"><div class="card-body">
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($config->share_item_id)
        <p><a class="btn btn-default" href="{{ route('admin.item.info', $config->share_item_id) }}">Вернуться к предмету</a></p>
    @endif
    <form action="{{ $config->exists ? route('admin.treasure_maps.update', $config->id) : route('admin.treasure_maps.store') }}" method="post">@csrf
        <div class="row">
            @foreach(['share_item_id' => 'Предмет-карта', 'reward_share_item_id' => 'Закрытая шкатулка за находку'] as $field => $label)
                @php $selectedId = old($field, $config->$field); @endphp
                <div class="col-md-6 mb-3"><label>{{ $label }}</label><select class="form-control treasure-item-select" name="{{ $field }}" required>
                    @if($selectedId && $selectedItems->has($selectedId))<option value="{{ $selectedId }}" selected>[{{ $selectedId }}] {{ $selectedItems[$selectedId]->name }}</option>@endif
                </select></div>
            @endforeach
            <div class="col-md-6 mb-3"><label>Репутация</label><select class="form-control" name="reputation_id" data-plugin-selectTwo required>@foreach($reputations as $rep)<option value="{{ $rep->id }}" @selected(old('reputation_id', $config->reputation_id) == $rep->id)>{{ $rep->name }}</option>@endforeach</select></div>
            @foreach(['required_reputation' => ['Порог репутации', 0, 100000000, 1], 'reputation_reward' => ['Репутация за находку', 10, 100000, 1], 'search_duration_seconds' => ['Поиск, секунд', 15, 3600, 1], 'success_chance_percent' => ['Шанс успеха, %', 70, 100, 0.001]] as $field => [$label, $default, $max, $step])
                <div class="col-md-3 mb-3"><label>{{ $label }}</label><input type="number" class="form-control" name="{{ $field }}" value="{{ old($field, $config->$field ?? $default) }}" min="{{ $field === 'required_reputation' ? 0 : $step }}" max="{{ $max }}" step="{{ $step }}" required></div>
            @endforeach
            <div class="col-md-12 mb-3"><small>При сборке каждой карты случайно выбирается территория, затем одна из её локаций вне данжей. Все территории с такими локациями имеют одинаковый шанс. Оба выбора закрепляются за экземпляром карты. Повторные проверки не меняют место клада. Четыре разных фрагмента и результат настраиваются в разделе «Сборки предметов», тип «Карты сокровищ».</small></div>
            <div class="col-md-6 mb-3"><label>Подсказка — русский</label><textarea name="clue_ru" class="form-control" rows="4" required>{{ old('clue_ru', $config->getTranslation('clue', 'ru', false)) }}</textarea></div>
            <div class="col-md-6 mb-3"><label>Подсказка — украинский</label><textarea name="clue_uk" class="form-control" rows="4">{{ old('clue_uk', $config->getTranslation('clue', 'uk', false)) }}</textarea></div>
            <div class="col-md-12 mb-3"><input type="hidden" name="is_active" value="0"><label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $config->is_active ?? true))> Поиск разрешён</label></div>
        </div>
        <button class="btn btn-primary">Сохранить</button> <a class="btn btn-default" href="{{ route('admin.treasure_maps.index') }}">Назад</a>
    </form>
    <p class="mt-3">Повторные неудачи не повышают шанс. Карта расходуется только при находке. Содержимое и условия взлома редактируются в карточке предмета-шкатулки.</p>
</div></section>
@endsection
@push('footer_scripts')
<script>
    $('.treasure-item-select').select2({ theme: 'bootstrap', width: '100%', placeholder: 'Выберите предмет', ajax: {
        url: @json(route('admin.api.items')), dataType: 'json', delay: 250,
        data: params => ({ q: params.term, page: params.page || 1 }), processResults: data => data
    }});
</script>
@endpush
