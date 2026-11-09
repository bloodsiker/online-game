@if($paymentAdministrator)
    <span class="payment-administrator" style="white-space: nowrap;">
        <a href="#" title="{{ __('Приватное сообщение') }}" aria-label="{{ __('Приватное сообщение') }}" onclick="return paymentAdministratorPrivate(@js($paymentAdministrator['name']))"><img src="{{ asset('img/icon/users-arrow.gif') }}" width="12" height="10" style="vertical-align: middle;" alt=""></a>
        <b>{{ $paymentAdministrator['name'] }}@if($paymentAdministrator['level'] !== null)&nbsp;[{{ $paymentAdministrator['level'] }}]@endif</b>
        <a href="{{ route('info.user', ['id' => $paymentAdministrator['id']]) }}" target="_blank" rel="noopener" title="{{ __('Информация о персонаже') }}" aria-label="{{ __('Информация о персонаже') }}" onclick="window.open(this.href, '', 'width=730,height=800,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" style="vertical-align: middle;" alt=""></a>
    </span>
@else
    —
@endif
