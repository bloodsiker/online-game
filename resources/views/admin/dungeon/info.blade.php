@extends('admin.layout.base')

@section('title')
    Данж: {{ $dungeon->name }}
@endsection

@section('body')

    <form action="{{ route('admin.dungeon.info', $dungeon->id) }}" method="post">
        {{ csrf_field() }}

        <div class="row">
            <div class="col-md-5">
                <section class="card">
                    <header class="card-header"><h2 class="card-title">Настройки смерти</h2></header>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="col-form-label">Тип данжа</label>
                            <select name="type" class="form-control">
                                @foreach(\App\Modules\Dungeon\Domain\Enums\DungeonType::cases() as $type)
                                    <option value="{{ $type->value }}" @selected(old('type', $dungeon->type->value) === $type->value)>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Поведение при смерти</label>
                            <select name="death_behavior" class="form-control">
                                @foreach($deathBehaviors as $behavior)
                                    <option value="{{ $behavior->value }}" @selected(old('death_behavior', $dungeon->death_behavior->value) === $behavior->value)>
                                        {{ $behavior->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('death_behavior')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="col-form-label">Локация возврата при смерти</label>
                            <select id="death-return-location" name="death_return_location_id" class="form-control">
                                @if(old('death_return_location_id'))
                                    @php
                                        $oldLocation = $locations->firstWhere('id', (int) old('death_return_location_id'));
                                    @endphp
                                    @if($oldLocation)
                                        <option value="{{ $oldLocation->id }}" selected>[{{ $oldLocation->id }}] {{ $oldLocation->name }}</option>
                                    @endif
                                @elseif($dungeon->deathReturnLocation)
                                    <option value="{{ $dungeon->deathReturnLocation->id }}" selected>
                                        [{{ $dungeon->deathReturnLocation->id }}] {{ $dungeon->deathReturnLocation->name }}
                                    </option>
                                @endif
                            </select>
                            @error('death_return_location_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            <p class="help-block mb-none">
                                Если не задано: для возврата в данж используется стартовая локация, для выброса наружу — обычная точка возврата данжа.
                            </p>
                        </div>
                    </div>
                </section>

                <div class="mb-3">
                    <button class="btn btn-primary">Сохранить</button>
                    <a href="{{ route('admin.dungeons') }}" class="btn btn-success">Назад</a>
                </div>
            </div>

            <div class="col-md-7">
                <section class="card">
                    <header class="card-header"><h2 class="card-title">Информация</h2></header>
                    <div class="card-body">
                        <table class="table table-bordered mb-none">
                            <tbody>
                            <tr>
                                <th width="220">ID</th>
                                <td>{{ $dungeon->id }}</td>
                            </tr>
                            <tr>
                                <th>Название</th>
                                <td>{{ $dungeon->name }}</td>
                            </tr>
                            <tr>
                                <th>Стартовая локация</th>
                                <td>
                                    @if($dungeon->firstLocation)
                                        [{{ $dungeon->firstLocation->id }}] {{ $dungeon->firstLocation->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Локация выхода</th>
                                <td>
                                    @if($dungeon->exitLocation)
                                        [{{ $dungeon->exitLocation->id }}] {{ $dungeon->exitLocation->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Обычная точка возврата</th>
                                <td>
                                    @if($dungeon->returnLocation)
                                        [{{ $dungeon->returnLocation->id }}] {{ $dungeon->returnLocation->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="card">
                    <header class="card-header"><h2 class="card-title">Локации данжа</h2></header>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-none">
                                <thead>
                                <tr>
                                    <th width="70">ID</th>
                                    <th>Название</th>
                                    <th width="100">Монстров</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($locations as $location)
                                    <tr>
                                        <td>{{ $location->id }}</td>
                                        <td>{{ $location->name }}</td>
                                        <td>{{ $location->count_monster }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">Нет локаций</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </form>

    <section class="card">
        <header class="card-header"><h2 class="card-title">Этажи и этапы</h2></header>
        <div class="card-body">
            <p class="help-block">Для башни таймер задаётся отдельно на каждый этаж. После уничтожения всех монстров игроки автоматически переходят выше.</p>

            @foreach($dungeon->stages as $stage)
                @include('admin.dungeon._stage-form', ['stage' => $stage])
            @endforeach

            @include('admin.dungeon._stage-form', ['stage' => null])
        </div>
    </section>

@push('footer_scripts')
<script>
    $('#death-return-location').select2({
        theme: 'bootstrap',
        placeholder: 'По умолчанию',
        allowClear: true,
        ajax: {
            url: '{{ route('admin.api.locations') }}',
            dataType: 'json',
            delay: 250,
            data: function (p) {
                return {
                    q: p.term,
                    page: p.page || 1,
                    dungeon_id: {{ $dungeon->id }}
                };
            },
            processResults: function (data, p) {
                p.page = p.page || 1;
                return { results: data.results, pagination: { more: data.pagination.more } };
            },
            cache: true
        },
        minimumInputLength: 0
    });

    document.querySelectorAll('[data-stage-form]').forEach(function (form) {
        const list = form.querySelector('.stage-monster-list');
        const template = form.querySelector('.stage-monster-template');
        const spawnType = form.querySelector('.stage-spawn-type');
        let nextIndex = list.querySelectorAll('.stage-monster-row').length;

        function updateSpawnFields() {
            const fixed = spawnType.value === 'fixed';
            form.querySelector('.stage-total-monsters').style.display = fixed ? 'none' : '';
            form.querySelectorAll('.stage-monster-quantity').forEach(el => el.style.display = fixed ? '' : 'none');
            form.querySelectorAll('.stage-monster-weight').forEach(el => el.style.display = fixed ? 'none' : '');
            form.querySelectorAll('.stage-monster-location').forEach(el => el.style.display = fixed ? '' : 'none');
            form.querySelector('.stage-monster-help').textContent = fixed
                ? ' — задайте количество и клетку для каждой группы; без клетки монстры появятся у входа.'
                : ' — общий размер волны задаётся выше, а вес определяет долю каждого вида.';
        }

        form.querySelector('.stage-monster-add').addEventListener('click', function () {
            list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
            updateSpawnFields();
        });

        list.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.stage-monster-remove');
            if (! removeButton) {
                return;
            }
            if (list.querySelectorAll('.stage-monster-row').length === 1) {
                return;
            }
            removeButton.closest('.stage-monster-row').remove();
        });

        spawnType.addEventListener('change', updateSpawnFields);
        updateSpawnFields();
    });
</script>
@endpush

@endsection
