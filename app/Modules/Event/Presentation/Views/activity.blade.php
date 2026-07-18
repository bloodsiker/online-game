{{-- Активности: дневные/недельные задания (партиал, подключается из event::index) --}}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr height="22">
    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
    <td class="tbl-shp-sml tt" valign="top" align="center">
        <table border="0" cellspacing="0" cellpadding="0">
            <tr height="22">
                @foreach(['daily' => 'Дневные', 'weekly' => 'Недельные'] as $groupKey => $groupLabel)
                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                    <td align="center" class="tbl-usi-hdr mbg">
                        <a href="{{ route('events', ['mode' => 'activity', 'group' => $groupKey]) }}"
                           class="tbl-shp_menu-link_{{ $group === $groupKey ? 'act' : 'inact' }}"
                           style="color:#FFE9BA !important;">{{ $groupLabel }}</a>
                    </td>
                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                @endforeach
            </tr>
        </table>
    </td>
    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
</tr>

<tr class="activity-spoiler-row">
    <td class="tbl-shp-sides ls">&nbsp;</td>
    <td class="tbl-usi_bg" align="center">
        <a href="javascript:void(0);" class="activity-spoiler-btn" onclick="return toggleActivitySpoiler('main');">
            <img id="activity_spoiler_img_main" src="{{ asset('main/images/flag_green.png') }}">
            <span id="activity_spoiler_text_main">Свернуть</span>
        </a>
    </td>
    <td class="tbl-shp-sides rs">&nbsp;</td>
</tr>

<tr id="activity_spoiler_content_main">
    <td class="tbl-shp-sides ls">&nbsp;</td>
    <td class="tbl-usi_bg" valign="top" align="center" style="padding: 6px 4px; zoom: 1;">
        <div class="store-grid" style="margin-left: 6px;">
            @foreach($activities as $activity)
                <div class="store-item {{ $activity->isRewardReady() ? 'activity-reward-ready' : '' }}" style="min-height:150px;">
                    <div style="padding:8px;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="60" valign="top">
                                    <div class="user-rewards__item-pic"
                                         data-id="{{ $activity->rewardItemId }}"
                                         onmouseover="showItemInfo(this,event,2)"
                                         onmouseout="showItemInfo(this,event,0)"
                                         style="display:inline-block;">
                                        <table width="60" height="60" cellpadding="0" cellspacing="0" border="0"
                                               background="{{ asset($activity->icon) }}">
                                            <tr>
                                                <td valign="bottom" style="position:relative; z-index:1;">
                                                    <div class="bpdig">{{ $activity->iconCount }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                                <td align="center" valign="top">
                                    <div class="b" style="font-size:14px; color:darkred;">
                                        {{ $activity->title }}
                                    </div>
                                    <br>
                                    <table class="coll w100 p10h p4v brd2-all">
                                        <col><col width="10%">
                                        <tr class="bg_l">
                                            <td class="brd2-top brd2-bt"><b>Прогресс:</b></td>
                                            <td class="brd2-top brd2-bt b red" nowrap>
                                                <b style="color:darkgreen;">{{ $activity->progressCurrent }}</b> / {{ $activity->progressTotal }}
                                            </td>
                                        </tr>
                                        <tr class="bg_l">
                                            <td class="brd2-top brd2-bt"><b>Бонус:</b></td>
                                            <td class="brd2-top brd2-bt" nowrap>{!! $activity->bonusHtml ?? 'Нет' !!}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top:6px;">
                            <div class="tab-content-items">
                                <div style="text-align:left;">
                                    <div>{!! $activity->descriptionHtml !!}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
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

<script>
    var activitySpoilerTitles = { main: 'Активности' };
    function toggleActivitySpoiler(type) {
        var row  = document.getElementById('activity_spoiler_content_' + type);
        var img  = document.getElementById('activity_spoiler_img_' + type);
        var text = document.getElementById('activity_spoiler_text_' + type);
        if (!row || !img || !text) return false;
        if (row.style.display === 'none') {
            row.style.display = '';
            img.src = '{{ asset('main/images/flag_green.png') }}';
            text.innerHTML = 'Свернуть';
        } else {
            row.style.display = 'none';
            img.src = '{{ asset('main/images/flag_red.png') }}';
            text.innerHTML = activitySpoilerTitles[type];
        }
        return false;
    }
</script>
