<!DOCTYPE html><html lang="{{ app()->getLocale() }}"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('payments.order') }} #{{ $order->id }}</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}"><link rel="stylesheet" href="{{ asset('css/premium.css') }}?v={{ filemtime(public_path('css/premium.css')) }}">
    <style>.payment-receipt{max-width:760px;margin:20px auto}.payment-receipt dl{display:grid;grid-template-columns:1fr 2fr;gap:12px}.payment-receipt dt{font-weight:bold}.payment-receipt dd{margin:0;overflow-wrap:anywhere}.payment-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:18px}.payment-actions form{margin:0}.payment-success{color:#247327;font-weight:bold}</style>
</head><body class="premium-page">
@include('premium_shop::_nav', ['activeMenu' => 'topup'])
@component('premium::_frame', ['title' => __('payments.order').' #'.$order->id])
    <div class="payment-receipt" data-payment-order>
        <dl><dt>{{ __('payments.number') }}</dt><dd>{{ $order->public_id }}</dd>
            <dt>{{ __('payments.created') }}</dt><dd>{{ $order->created_at->format('d.m.Y H:i') }}</dd>
            <dt>{{ __('payments.amount') }}</dt><dd><b>{{ $order->formattedAmount() }}</b></dd>
            <dt>{{ __('payments.diamonds') }}</dt><dd><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" alt=""> <b>{{ format_money($order->diamonds) }}</b></dd>
            <dt>{{ __('payments.state') }}</dt><dd><b data-order-status>{{ $order->status->label() }}</b></dd>
        </dl>
        <p data-order-pending @if($order->status !== \App\Modules\Payments\Domain\Enums\PaymentOrderStatus::PENDING) hidden @endif>{{ __('payments.manual_notice') }}</p>
        <p class="payment-success" data-order-paid @if($order->credited_at === null) hidden @endif>{{ __('payments.credited') }}</p>
        <p data-order-note>{{ $order->review_note }}</p>
        <p class="premium-error" data-order-error role="status">{{ $errors->first() }}</p>
        <div class="payment-actions">
            <span class="butt1 pointer"><span><button class="butt1" type="button" data-order-check>{{ __('payments.check') }}</button></span></span>
            <form method="post" action="{{ route('payments.orders.cancel', $order->public_id) }}" data-order-cancel @if($order->status !== \App\Modules\Payments\Domain\Enums\PaymentOrderStatus::PENDING) hidden @endif>@csrf<span class="butt1 pointer"><span><button class="butt1" type="submit">{{ __('payments.cancel') }}</button></span></span></form>
            <a href="{{ route('premium.topup') }}">{{ __('payments.back') }}</a>
        </div>
    </div>
@endcomponent
@php
    $paymentTracking = [
        'url' => route('payments.orders.status', $order->public_id), 'loginUrl' => route('index'),
        'status' => $order->status->value, 'error' => __('payments.status_error'),
    ];
@endphp
<script>window.paymentOrderTracking = @json($paymentTracking);</script>
<script src="{{ asset('js/payment-order.js') }}?v={{ filemtime(public_path('js/payment-order.js')) }}"></script>
</body></html>
