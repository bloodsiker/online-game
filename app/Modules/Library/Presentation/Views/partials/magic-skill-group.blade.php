<table class="library-game-frame library-magic-group" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" align="center">
            <table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                    <td class="tbl-usi_label-center">{{ $title }}</td>
                    <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="20" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding: 8px 10px;">
            <div style="display: flex; flex-wrap: wrap; align-items: flex-start; gap: 8px;">{!! $cards !!}</div>
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18">
        <td width="20" class="tbl-shp-sml lb"><b></b></td>
        <td class="tbl-shp-sml bb">&nbsp;</td>
        <td width="20" class="tbl-shp-sml rb"><b></b></td>
    </tr>
    </tbody>
</table>
