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
    <form method="get" class="row mb-3">
        <div class="col-md-3"><label>Статус<select class="form-control" name="status"><option value="">Все</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></label></div>
        <div class="col-md-2"><label>ID игрока<input class="form-control" type="number" name="user_id" min="1" value="{{ request('user_id') }}"></label></div>
        <div class="col-md-4"><label>Номер ордера (UUID)<input class="form-control" name="order" value="{{ request('order') }}"></label></div>
        <div class="col-md-3"><button class="btn btn-primary" type="submit">Применить</button> <a class="btn btn-default" href="{{ route('admin.payment_orders.index') }}">Сбросить</a></div>
    </form>
    <div class="table-responsive"><table class="table table-bordered table-hover"><thead><tr><th>Ордер</th><th>Создан</th><th>Игрок</th><th>Сумма</th><th>Бриллианты</th><th>Статус</th><th>Подтвердил</th></tr></thead><tbody>
    @forelse($orders as $order)<tr><td><a href="{{ route('admin.payment_orders.show', $order->public_id) }}">#{{ $order->id }}</a></td><td>{{ $order->created_at->format('d.m.Y H:i') }}</td><td><a href="{{ url('/admin/player/'.$order->user_id) }}">{{ $order->user?->name }} [ID {{ $order->user_id }}]</a></td><td>{{ $order->formattedAmount() }}</td><td>{{ format_money($order->diamonds) }}</td><td>{{ $order->status->label() }}</td><td>{{ $order->reviewer?->name }} {{ $order->credited_at?->format('d.m.Y H:i') }}</td></tr>@empty<tr><td colspan="7">Ордеров ещё нет.</td></tr>@endforelse
    </tbody></table></div>{{ $orders->links() }}
</div></section>
@endsection
