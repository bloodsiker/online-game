<table class="player-trade__frame" border="0" cellspacing="0" cellpadding="0" role="presentation">
    <tbody>
    <tr height="22">
        <td width="20" class="tbl-shp-sml lt"></td>
        <td class="tbl-shp-sml tt" align="center" valign="top">
            <table class="player-trade__label" border="0" cellspacing="0" cellpadding="0" role="presentation">
                <tr height="22">
                    <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                    <td class="tbl-usi_label-center" align="center"><span>{{ $title }}</span></td>
                    <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
                </tr>
            </table>
        </td>
        <td width="20" class="tbl-shp-sml rt"></td>
    </tr>
    <tr class="player-trade__frame-middle">
        <td class="tbl-shp-sides ls"></td>
        <td class="tbl-usi_bg player-trade__frame-content" valign="top">{{ $slot }}</td>
        <td class="tbl-shp-sides rs"></td>
    </tr>
    <tr height="18">
        <td class="tbl-shp-sml lb"></td>
        <td class="tbl-shp-sml bb"></td>
        <td class="tbl-shp-sml rb"></td>
    </tr>
    </tbody>
</table>
