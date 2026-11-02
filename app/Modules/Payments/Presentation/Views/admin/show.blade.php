@extends('admin.layout.base')
@section('title', 'Ордер #'.$order->id)
@section('body')
<section class="card"><div class="card-body">
    <p><a href="{{ route('admin.payment_orders.index') }}">← Платежи и ордера</a></p>
    @if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <dl class="row"><dt class="col-sm-3">Номер</dt><dd class="col-sm-9">{{ $order->public_id }}</dd>
        <dt class="col-sm-3">Игрок</dt><dd class="col-sm-9"><a href="{{ url('/admin/player/'.$order->user_id) }}">{{ $order->user?->name }} [{{ $order->user_id }}]</a></dd>
        <dt class="col-sm-3">Сумма</dt><dd class="col-sm-9">{{ $order->formattedAmount() }}</dd>
        <dt class="col-sm-3">Курс за бриллиант</dt><dd class="col-sm-9">{{ number_format($order->diamond_price_minor / 100, 2, '.', ' ') }} {{ $order->currency }}</dd>
        <dt class="col-sm-3">К начислению</dt><dd class="col-sm-9">{{ format_money($order->diamonds) }} бриллиантов</dd>
        <dt class="col-sm-3">Статус</dt><dd class="col-sm-9">{{ $order->status->label() }}</dd>
        <dt class="col-sm-3">Подтвердил / рассмотрел</dt><dd class="col-sm-9">{{ $order->reviewer?->name }} {{ $order->reviewed_at?->format('d.m.Y H:i') }}</dd>
        <dt class="col-sm-3">Комментарий</dt><dd class="col-sm-9">{{ $order->review_note }}</dd>
        @if($order->credited_at)<dt class="col-sm-3">Начислено</dt><dd class="col-sm-9">{{ $order->credited_at->format('d.m.Y H:i:s') }}, баланс после начисления: {{ format_money($order->diamond_balance_after) }}</dd>@endif
    </dl>
    @if($order->status === \App\Modules\Payments\Domain\Enums\PaymentOrderStatus::PENDING)
        <div class="row"><div class="col-md-6">
            <form action="{{ route('admin.payment_orders.confirm', $order->public_id) }}" method="post">@csrf
                <label>Комментарий<textarea class="form-control" name="note" maxlength="1000">{{ old('note') }}</textarea></label><br>
                <label><input type="checkbox" name="confirmed_payment" value="1" required> Я проверил поступление {{ $order->formattedAmount() }} по этому ордеру</label><br>
                <button class="btn btn-success" type="submit">Подтвердить оплату и начислить {{ format_money($order->diamonds) }} бриллиантов</button>
            </form>
        </div><div class="col-md-6">
            <form action="{{ route('admin.payment_orders.reject', $order->public_id) }}" method="post">@csrf
                <label>Причина отклонения (видна игроку)<textarea class="form-control" name="note" maxlength="1000" required></textarea></label><br>
                <button class="btn btn-danger" type="submit">Отклонить ордер</button>
            </form>
        </div></div>
    @endif
</div></section>
<section class="card mt-3"><div class="card-body"><h3>История статусов</h3><table class="table table-bordered"><thead><tr><th>Дата</th><th>Кто</th><th>Было</th><th>Стало</th><th>Комментарий</th></tr></thead><tbody>
@foreach($events as $event)<tr><td>{{ $event->created_at }}</td><td>{{ $event->actor_name }} [{{ $event->actor_id }}]</td><td>{{ $event->from_status ? __('payments.status.'.$event->from_status) : '—' }}</td><td>{{ __('payments.status.'.$event->to_status) }}</td><td>{{ $event->note }}</td></tr>@endforeach
</tbody></table></div></section>
@endsection
