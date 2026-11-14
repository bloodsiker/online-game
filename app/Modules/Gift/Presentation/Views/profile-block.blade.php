{{-- Блок «Подарки» в публичной информации игрока (user::info). $giftsBlock — PlayerGiftsBlockDTO. --}}
@if($giftsBlock && $giftsBlock->total > 0)
    @include('gift::partials.styles')
    <div style="height:10px;font-size:1px;">&nbsp;</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" id="player-gifts">
        <tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                    <tr height="22">
                        <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                        <td align="center" class="tbl-usi-hdr mbg">{{ __('gift.title') }} ({{ $giftsBlock->total }})</td>
                        <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" align="left" style="padding:3px 0 8px 0;">
                <div class="player-gifts">@foreach($giftsBlock->gifts as $gift)@include('gift::partials.gift-card', ['gift' => $gift])@endforeach</div>
                @if($giftsBlock->total > $giftsBlock->gifts->count())
                    <div class="player-gifts__all"><a href="{{ $giftsBlock->allUrl }}">{{ __('gift.all', ['count' => $giftsBlock->total]) }}</a></div>
                @endif
            </td>
            <td class="tbl-shp-sides rs">&nbsp;</td>
        </tr>
        <tr height="18">
            <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
            <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
            <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
        </tr>
        </tbody>
    </table>
@endif
