@if($orders)
    @component('premium::_frame', ['title' => __('payments.my_orders')])
        <table class="coll w100 p10h p4v brd2" border="0" cellspacing="0" cellpadding="0">
            <thead><tr><th>{{ __('payments.order') }}</th><th>{{ __('payments.created') }}</th><th>{{ __('payments.amount') }}</th><th>{{ __('payments.diamonds') }}</th><th>{{ __('payments.state') }}</th><th></th></tr></thead>
            <tbody>@forelse($orders as $order)
                <tr><td><a href="{{ route('payments.orders.show', $order->public_id) }}">#{{ $order->id }}</a></td><td>{{ $order->created_at->format('d.m.Y H:i') }}</td><td>{{ $order->formattedAmount() }}</td><td>{{ format_money($order->diamonds) }}</td><td>{{ $order->status->label() }}</td><td align="center"><span class="butt1 pointer"><span><button class="butt1" type="button" onclick="window.location.href=this.dataset.href" data-href="{{ route('payments.orders.show', $order->public_id) }}">{{ $order->status === \App\Modules\Payments\Domain\Enums\PaymentOrderStatus::PENDING ? __('payments.open_pay') : __('payments.open') }}</button></span></span></td></tr>
            @empty<tr><td colspan="6" align="center">{{ __('payments.empty') }}</td></tr>@endforelse</tbody>
        </table>
        {{ $orders->links() }}
    @endcomponent
@endif
