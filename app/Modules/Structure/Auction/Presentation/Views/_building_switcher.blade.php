<table class="auction-building-switcher" border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-bottom: 10px">
    <tbody>
    <tr>
        <td align="center" style="padding: 4px 6px 2px;">
            <span class="butt1 pointer"><span><button class="butt1 shop" data-href="{{ route('auction', ['id' => $commissionShop->id]) }}" type="button">Комиссионный магазин</button></span></span>
            <span class="butt1 pointer"><span><button class="butt1 shop" data-href="{{ route('auction.exchange', ['id' => $exchange->id]) }}" type="button">Биржа</button></span></span>
        </td>
    </tr>
    </tbody>
</table>

<script>
    document.querySelectorAll('.auction-building-switcher .shop').forEach(function (button) {
        button.addEventListener('click', function () {
            const href = this.getAttribute('data-href');

            if (href) {
                window.location.href = href;
            }
        });
    });
</script>
