<table class="coll brd2-all bg_l p10h p2v blacksmith-summary" width="100%" border="0" cellspacing="0" cellpadding="0"
       style="margin-bottom:10px; background-image:url('{{ asset('img/bg/info/bg_l.gif') }}');">
    <tbody>
    <tr>
        <td align="left" style="color:#000;"><b style="color:#000;">{{ __('Кузня:') }}</b> {{ $sectionTitle }}</td>
        <td align="right" style="color:#955c4a;">
            <b>{{ __('Монеты:') }}</b>
            <b class="redd"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->money) }}</b>
            &nbsp;&nbsp;<b>{{ __('Бриллианты:') }}</b>
            <b class="redd"><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->diamond) }}</b>
        </td>
    </tr>
    </tbody>
</table>
