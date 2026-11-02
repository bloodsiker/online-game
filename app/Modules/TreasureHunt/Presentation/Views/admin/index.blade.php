@extends('admin.layout.base')
@section('title', 'Карты сокровищ')
@section('body')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<section class="card"><div class="card-body">
    <a class="btn btn-primary mb-3" href="{{ route('admin.treasure_maps.create') }}">Добавить карту</a>
    <p>Предмет-карту создайте в справочнике предметов с типом «Карта сокровищ» и признаком «Активный: Нет». Создайте здесь настройки поиска, затем активируйте предмет. Содержимое, замок и ловушка найденной шкатулки настраиваются на странице самой шкатулки.</p>
    <table class="table table-bordered"><thead><tr><th>Карта</th><th>Порог репутации</th><th>Шанс</th><th>Поиск</th><th>Награда</th><th>Активна</th><th></th></tr></thead><tbody>
    @foreach($configs as $config)
        <tr><td><a href="{{ route('admin.item.info', $config->share_item_id) }}">[{{ $config->share_item_id }}] {{ $config->item->name }}</a></td><td>{{ $config->required_reputation }} — {{ $config->reputation->name }}</td><td>{{ $config->success_chance_percent }}%</td><td>{{ $config->search_duration_seconds }} сек.</td><td><a href="{{ route('admin.item.info', $config->reward_share_item_id) }}">{{ $config->reward->name }}</a><br>+{{ $config->reputation_reward }} репутации</td><td>{{ $config->is_active ? 'Да' : 'Нет' }}</td><td><a class="btn btn-default" href="{{ route('admin.treasure_maps.edit', $config->id) }}">Изменить</a></td></tr>
    @endforeach
    </tbody></table>{{ $configs->links() }}
</div></section>
<section class="card mt-3"><header class="card-header"><h2 class="card-title">Бонусы уровней репутации</h2></header><div class="card-body">
    <form action="{{ route('admin.treasure_maps.bonuses') }}" method="post">@csrf
        <p>Бонус шанса прибавляется в процентных пунктах, итог не выше 100%. Ускорение сокращает длительность попытки. Применяется только наивысшая достигнутая ступень, бонусы не суммируются.</p>
        <table class="table table-bordered"><thead><tr><th>Репутация / ступень</th><th>От очков</th><th>Бонус шанса, п.п.</th><th>Ускорение, %</th></tr></thead><tbody>
        @foreach($tiers as $tier)<tr><td>{{ $tier->reputation->name }} / {{ $tier->medal_name }}</td><td>{{ $tier->min_points }}</td><td><input type="number" min="0" max="100" step="0.001" name="tiers[{{ $tier->id }}][search_chance_bonus_percent]" value="{{ $bonuses->get($tier->id)?->search_chance_bonus_percent ?? 0 }}" required></td><td><input type="number" min="0" max="90" step="0.01" name="tiers[{{ $tier->id }}][search_speed_bonus_percent]" value="{{ $bonuses->get($tier->id)?->search_speed_bonus_percent ?? 0 }}" required></td></tr>@endforeach
        </tbody></table><button class="btn btn-primary" @disabled($tiers->isEmpty())>Сохранить бонусы</button>
    </form>
</div></section>
<section class="card mt-3"><header class="card-header"><h2 class="card-title">Найденные сокровища</h2></header><div class="card-body">
    <table class="table table-bordered"><thead><tr><th>Когда</th><th>Игрок</th><th>Локация</th><th>Шкатулка (экземпляр)</th><th>Репутация</th></tr></thead><tbody>
    @foreach($history as $hunt)<tr><td>{{ $hunt->found_at }}</td><td><a href="{{ url('/admin/player/'.$hunt->user_id) }}">#{{ $hunt->user_id }}</a></td><td>{{ $hunt->target_location_id }}</td><td>{{ $hunt->reward_item_id ?? 'Предмет уже использован' }}</td><td>+{{ $hunt->reputation_awarded }}</td></tr>@endforeach
    </tbody></table>{{ $history->links() }}
</div></section>
@endsection
