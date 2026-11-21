@extends('admin.layout.base')
@section('title', 'Платежи и ордера')
@section('body')
<section class="card"><div class="card-body">
    <h3>Покупка бриллиантов — ручное подтверждение</h3>
    @if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <p>Создание ордера не означает оплату. Подтверждайте только проверенные оплаты. Курс и сумма фиксируются в ордере; повторное подтверждение не начисляет бриллианты повторно.</p>
    <form action="{{ route('admin.payment_orders.settings') }}" method="post" class="row mb-4">@csrf
        <div class="col-md-3"><label>Цена 1 бриллианта, USD<input class="form-control" type="number" name="diamond_price" step="0.01" min="0.01" required value="{{ old('diamond_price', $settings->diamond_price_minor > 0 ? number_format($settings->diamond_price_minor / 100, 2, '.', '') : '') }}"></label></div>
        <div class="col-md-3"><label>Минимальная сумма, USD<input class="form-control" type="number" name="minimum_amount" step="0.01" min="0.01" required value="{{ old('minimum_amount', $settings->minimum_amount_minor > 0 ? number_format($settings->minimum_amount_minor / 100, 2, '.', '') : '') }}"></label></div>
        <div class="col-md-3"><input type="hidden" name="is_enabled" value="0"><label><input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $settings->is_enabled))> Создание ордеров включено</label></div>
        <div class="col-md-3"><button class="btn btn-primary" type="submit">Сохранить настройки</button></div>
    </form>
    <h4>Курс НБУ (USD → UAH) для оплаты картой</h4>
    <p>
        @if($currentRate)
            Текущий курс: <b>{{ number_format((float) $currentRate->rate, 4, '.', ' ') }} грн</b> за 1 USD (на {{ \Carbon\Carbon::parse($currentRate->rate_date)->format('d.m.Y') }}, загружен {{ \Carbon\Carbon::parse($currentRate->updated_at)->format('d.m.Y H:i') }}).
            1 бриллиант = {{ number_format($settings->diamond_price_minor / 100 * (float) $currentRate->rate, 2, '.', ' ') }} грн.
        @else
            Курс ещё не загружен — используется запасной {{ config('services.monobank.uah_per_usd') }} грн (MONOBANK_UAH_PER_USD).
        @endif
        Обновляется задачей планировщика «Курс НБУ (USD/UAH)» раз в сутки ночью.
    </p>
    <form action="{{ route('admin.payment_orders.sync_rate') }}" method="post" class="mb-3">@csrf
        <button class="btn btn-default btn-sm" type="submit">Обновить курс НБУ сейчас</button>
    </form>
    @if($rateHistory->isNotEmpty())
        <details class="mb-4"><summary>История курсов (последние {{ $rateHistory->count() }} дн.)</summary>
            <table class="table table-bordered table-sm mt-2" style="max-width:420px"><thead><tr><th>Дата</th><th>Курс, грн/USD</th><th>Изм.</th></tr></thead><tbody>
            @foreach($rateHistory as $i => $row)
                @php($prev = $rateHistory[$i + 1] ?? null)
                @php($diff = $prev ? (float) $row->rate - (float) $prev->rate : null)
                <tr><td>{{ \Carbon\Carbon::parse($row->rate_date)->format('d.m.Y') }}</td><td>{{ number_format((float) $row->rate, 4, '.', ' ') }}</td><td>@if($diff !== null)<span style="color:{{ $diff > 0 ? '#c0392b' : ($diff < 0 ? '#247327' : '#777') }}">{{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 4, '.', ' ') }}</span>@endif</td></tr>
            @endforeach
            </tbody></table>
        </details>
    @endif
    <form method="get" class="row mb-3">
        <div class="col-md-3"><label>Статус<select class="form-control" name="status"><option value="">Все</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></label></div>
        <div class="col-md-2"><label>ID игрока<input class="form-control" type="number" name="user_id" min="1" value="{{ request('user_id') }}"></label></div>
        <div class="col-md-4"><label>Номер ордера (UUID)<input class="form-control" name="order" value="{{ request('order') }}"></label></div>
        <div class="col-md-3"><button class="btn btn-primary" type="submit">Применить</button> <a class="btn btn-default" href="{{ route('admin.payment_orders.index') }}">Сбросить</a></div>
    </form>
    <div class="table-responsive"><table class="table table-bordered table-hover"><thead><tr><th>Ордер</th><th>Создан</th><th>Игрок</th><th>Сумма</th><th>Бриллианты</th><th>Статус</th><th>Подтвердил</th></tr></thead><tbody>
    @forelse($orders as $order)<tr><td><a href="{{ route('admin.payment_orders.show', $order->public_id) }}">#{{ $order->id }}</a></td><td>{{ $order->created_at->format('d.m.Y H:i') }}</td><td><a href="{{ url('/admin/player/'.$order->user_id) }}">{{ $order->user?->name }} [ID {{ $order->user_id }}]</a></td><td>{{ $order->formattedAmount() }}@if($order->formattedCharge())<br><small>{{ $order->formattedCharge() }}</small>@endif</td><td>{{ format_money($order->diamonds) }}</td><td>{{ $order->status->label() }}</td><td>{{ $order->reviewer?->name }} {{ $order->credited_at?->format('d.m.Y H:i') }}</td></tr>@empty<tr><td colspan="7">Ордеров ещё нет.</td></tr>@endforelse
    </tbody></table></div>{{ $orders->links() }}
</div></section>
@endsection
