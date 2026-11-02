@extends('admin.layout.base')
@section('title', 'Премиум')
@section('body')
    <section class="card"><div class="card-body">
        <h3>Настройки премиума</h3>
        <p class="alert alert-info">Первый обмен запускает период на 30 дней. Повторные обмены повышают уровень без продления срока. Бонусы текущего уровня заменяют предыдущие, а не складываются. Изменения настроек действуют и для активного премиума.</p>
        @if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form action="{{ route('admin.premium.save') }}" method="post">
            @csrf
            <label>Монет за 1 бриллиант <input class="form-control" type="number" name="gold_per_diamond" min="1" max="1000000" required value="{{ old('gold_per_diamond', $rate ?: '') }}"></label>
            <div class="table-responsive mt-3"><table class="table table-bordered">
                <thead><tr><th>Условие / бонус</th>@foreach($levels as $level)<th>Уровень {{ $level->level }}</th>@endforeach</tr></thead>
                <tbody><tr><th>Обменяно бриллиантов за период (порог)</th>@foreach($levels as $level)<td><input class="form-control" type="number" step="1" min="1" max="9999999" required name="levels[{{ $level->level }}][threshold]" value="{{ old('levels.'.$level->level.'.threshold', $level->required_diamond_cents === null ? '' : intdiv($level->required_diamond_cents, 100)) }}"></td>@endforeach</tr>
                @foreach($bonusTypes as $bonus)
                    <tr><th>{{ $bonus->label() }}{{ $bonus->isBoolean() || $bonus === \App\Modules\Premium\Domain\Enums\PremiumBonus::BAG_SLOTS ? '' : ', %' }}</th>
                        @foreach($levels as $level)
                            @php $field = 'levels.'.$level->level.'.bonuses.'.$bonus->value; $value = old($field, $level->bonuses[$bonus->value] ?? 0); @endphp
                            <td>
                                <input type="hidden" name="levels[{{ $level->level }}][bonus_enabled][{{ $bonus->value }}]" value="0">
                                <label><input type="checkbox" name="levels[{{ $level->level }}][bonus_enabled][{{ $bonus->value }}]" value="1" @checked(old('levels.'.$level->level.'.bonus_enabled.'.$bonus->value, $level->bonusEnabled($bonus)))> Включён</label>
                                @if($bonus->isBoolean())
                                <select class="form-control" name="levels[{{ $level->level }}][bonuses][{{ $bonus->value }}]"><option value="0" @selected(!$value)>Нет</option><option value="1" @selected($value)>Да</option></select>
                            @else
                                <input class="form-control" type="number" min="0" max="{{ $bonus->max() }}" step="{{ $bonus === \App\Modules\Premium\Domain\Enums\PremiumBonus::BAG_SLOTS ? '1' : '0.01' }}" required name="levels[{{ $level->level }}][bonuses][{{ $bonus->value }}]" value="{{ $value }}">
                            @endif</td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table></div>
            <p>Снимите «Включён», чтобы скрыть бонус на выбранном уровне и отключить его действие. Значение сохраняется для повторного включения.</p>
            <p>Дополнительный ресурс — прибавка к шансу побочного дропа при добыче (в процентных пунктах, итог не выше 100%). При окончании премиума предметы из рюкзака не удаляются: уменьшается только лимит вместимости.</p>
            <button class="btn btn-primary" type="submit">Сохранить</button>
        </form>
    </div></section>
    <section class="card mt-3"><div class="card-body"><h3>История обменов</h3>
        <table class="table table-bordered"><thead><tr><th>Дата</th><th>Игрок</th><th>Период</th><th>Бриллианты</th><th>Курс</th><th>Монеты</th></tr></thead><tbody>
        @forelse($exchanges as $exchange)<tr><td>{{ $exchange->created_at }}</td><td><a href="{{ url('/admin/player/'.$exchange->user_id) }}">{{ $exchange->player_name }}</a></td><td>{{ $exchange->premium_period_id }}</td><td>{{ format_money($exchange->diamond_cents / 100) }}</td><td>{{ $exchange->gold_per_diamond }}</td><td>{{ format_money($exchange->gold_amount) }}</td></tr>@empty<tr><td colspan="6">Обменов ещё нет.</td></tr>@endforelse
        </tbody></table>{{ $exchanges->links() }}
    </div></section>
@endsection
