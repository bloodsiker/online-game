@extends('admin.layout.base')

@section('title')
    Реферальная система — Этапы наград
@endsection

@section('body')

<div class="row">
    <div class="col-md-8">
        <section class="card">
            <header class="card-header">
                <h2 class="card-title">Этапы наград</h2>
            </header>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <table class="table table-bordered table-hover mb-none">
                    <thead>
                        <tr>
                            <th width="100">Уровень</th>
                            <th width="120">Тип</th>
                            <th>Награда</th>
                            <th width="70">Кол-во</th>
                            <th width="60"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($stages as $stage)
                        <tr>
                            <td><b>{{ $stage->level_threshold }}</b></td>
                            <td>{{ $stage->reward_type->label() }}</td>
                            <td>
                                @if($stage->reward_type === \App\Enums\ReferralRewardType::ITEM)
                                    {{ $stage->rewardItem?->name ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $stage->reward_value }}</td>
                            <td>
                                <a href="{{ route('admin.referral.stage.delete', $stage->id) }}"
                                   class="btn btn-xs btn-danger"
                                   onclick="return confirm('Удалить этап?')">Удалить</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Нет этапов</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="col-md-4">
        <section class="card">
            <header class="card-header">
                <h2 class="card-title">Добавить этап</h2>
            </header>
            <div class="card-body">
                <form action="{{ route('admin.referral.stage.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Уровень реферала</label>
                        <input type="number" name="level_threshold" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Тип награды</label>
                        <select name="reward_type" id="reward_type" class="form-control"
                                onchange="toggleItemSelect(this.value)">
                            @foreach($rewardTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" id="item_select_wrap" style="display:none">
                        <label>Предмет</label>
                        <select name="reward_item_id" class="form-control">
                            <option value=""></option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Количество</label>
                        <input type="number" name="reward_value" class="form-control" min="1" value="1" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </form>
            </div>
        </section>

        <section class="card mt-3">
            <div class="card-body">
                <a href="{{ route('admin.referral.stats') }}" class="btn btn-default btn-block">
                    Статистика рефералов
                </a>
            </div>
        </section>
    </div>
</div>

<script>
function toggleItemSelect(type) {
    document.getElementById('item_select_wrap').style.display = type === 'item' ? 'block' : 'none';
}
</script>

@endsection