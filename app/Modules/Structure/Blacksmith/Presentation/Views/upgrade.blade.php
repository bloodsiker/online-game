<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заточка</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .brd2-top { border-top: 1px solid #db9f73; }
        .brd2, .brd2 td { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .pointer, .pointer input { cursor: pointer; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .regcolor, .regcolor * { color: #955c4a; }
        .collection-slot {
            display: inline-block; position: relative;
            width: 52px; height: 76px; overflow: hidden; vertical-align: top;
        }
        .collection-slot__img {
            display: block; width: 60px; height: 60px;
            padding: 1px; background: url(../images/slot-empty.png) no-repeat;
        }
        .collection-slot__qty {
            display: block; font-weight: 700; text-align: center; font-size: 11px;
        }
        .collection-resource-img { width: 100%; }

        /* Item & scroll rows */
        .item-row, .scroll-row { cursor: pointer; border-bottom: 1px solid #e8c899; }
        .item-row:hover, .scroll-row:hover { background: #ffefd5; }
        .item-row.selected, .scroll-row.selected { background: #ffe0a0; font-weight: bold; }
        .item-row td, .scroll-row td { padding: 3px 4px; }

        /* Level badge */
        .lvl-badge { color: #2255aa; font-weight: bold; }
        .lvl-max { color: #888; }

        /* Золотистая пергаментная рамка (common-corners/tb/lr + common-bg) — как на странице «Камни» */
        .gp-frame-tl, .gp-frame-tr, .gp-frame-bl, .gp-frame-br {
            background: url('/img/bg/common-corners.png') no-repeat;
            font-size: 0; line-height: 0;
        }
        .gp-frame-t, .gp-frame-b {
            background: url('/img/bg/common-tb.png') repeat-x;
            font-size: 0; line-height: 0;
        }
        .gp-frame-l, .gp-frame-r {
            background: url('/img/bg/common-lr.png') repeat-y;
            font-size: 0; line-height: 0;
        }
        .gp-frame-tl { background-position: 0 0; }
        .gp-frame-tr { background-position: 100% 0; }
        .gp-frame-bl { background-position: 0 100%; }
        .gp-frame-br { background-position: 100% 100%; }
        .gp-frame-t { background-position: 0 0; }
        .gp-frame-b { background-position: 0 100%; }
        .gp-frame-l { background-position: 0 0; }
        .gp-frame-r { background-position: 100% 0; }
        .gp-frame-bg { background: url('/img/bg/common-bg.png'); padding: 10px; }
        .upgrade-icon { display: inline-block; width: 60px; height: 60px; padding: 5px 6px 6px; background: url('/main/images/user-reward-frame.png') no-repeat; cursor: pointer; }
        .upgrade-icon img { width: 60px; height: 60px; object-fit: contain; }
        .upgrade-chance-bar { height: 10px; background: #ddd; border: 1px solid #b08060; margin: 3px 0; }
        .upgrade-chance-fill { height: 100%; background: #55aa33; }
        .upgrade-chance-fill.medium { background: #ddaa00; }
        .upgrade-chance-fill.low { background: #cc3300; }

        /* Result flash */
        .flash-success { background: #d4f0c0; border: 1px solid #60a840; color: #2a6010; padding: 6px 10px; margin-bottom: 8px; }
        .flash-fail    { background: #f8dcd0; border: 1px solid #c05030; color: #7a2010; padding: 6px 10px; margin-bottom: 8px; }
        .flash-destroy { background: #300000; border: 1px solid #c00000; color: #ff6060; padding: 6px 10px; margin-bottom: 8px; font-weight: bold; }

        /* Progress bar */
        .upgrade-progress-wrap { width: 100%; height: 12px; background: #ddd; border: 1px solid #b08060; margin: 8px 0 4px; display: none; }
        .upgrade-progress-fill { height: 100%; width: 0; background: linear-gradient(to right, #c47a20, #f0a840); transition: width 1s linear; }
    </style>
</head>
<body class="regcolor" leftmargin="0" rightmargin="0">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('blacksmith::_tabs', ['activeTab' => 'upgrade'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10px 6px;">

            {{-- Flash message --}}
            @if(session('message'))
                @php
                    $destroyed = session('upgrade_destroyed', false);
                    $success   = session('upgrade_success', false);
                    $flashClass = $destroyed ? 'flash-destroy' : ($success ? 'flash-success' : 'flash-fail');
                @endphp
                <div class="{{ $flashClass }}">{{ session('message') }}</div>
            @endif

            {{-- Coins --}}
            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td align="left"><b>Кузня:</b> Заточка</td>
                    <td align="right" nowrap="" style="color:#955c4a;">
                        <b>Монеты:</b> <b class="redd"><span title="Монеты"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->money) }}</b>
                        &nbsp;&nbsp;<b>Бриллианты:</b> <b class="redd"><span title="Бриллианты"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->diamond) }}</b>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>

            <form action="{{ route('blacksmith.upgrade.process', ['id' => $blacksmith->id]) }}" method="post" id="upgrade-form">
                @csrf
                <input type="hidden" name="item_id"         id="selected-item-id"         value="">
                <input type="hidden" name="base_scroll_id"  id="selected-base-scroll-id"  value="">
                <input type="hidden" name="bonus_scroll_id" id="selected-bonus-scroll-id" value="">

                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr valign="top">

                        {{-- Left: item list --}}
                        <td width="31%">
                            <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                                <tbody>
                                <tr height="22">
                                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                    <td align="center" class="tbl-usi-hdr mbg">Предметы для заточки</td>
                                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                </tr>
                                </tbody>
                            </table>

                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="8" height="10" class="gp-frame-tl"></td>
                                    <td height="10" class="gp-frame-t"></td>
                                    <td width="8" height="10" class="gp-frame-tr"></td>
                                </tr>
                                <tr>
                                    <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                    <td class="gp-frame-bg">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tbody id="item-list">
                                            @forelse($items as $slot)
                                                <tr class="item-row" data-item-id="{{ $slot['id'] }}"
                                                    data-name="{{ $slot['name'] }}"
                                                    data-level="{{ $slot['level'] }}"
                                                    data-pity="{{ $slot['pity'] }}"
                                                    data-img="{{ $slot['image'] }}">
                                                    <td width="30" style="padding:3px 4px;">
                                                        <img src="{{ $slot['image'] }}" width="40" height="40"
                                                             data-id="{{ $slot['id'] }}"
                                                             onmouseover="showItemInfo(this,event,2)"
                                                             onmouseout="showItemInfo(this,event,0)">
                                                    </td>
                                                    <td style="padding:3px 4px;">
                                                        {{ $slot['name'] }}
                                                        @if($slot['level'] > 0)
                                                            <span class="lvl-badge">+{{ $slot['level'] }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" style="padding:8px; color:#888; text-align:center;">Нет предметов для заточки</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                    <td width="8" class="gp-frame-r"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                </tr>
                                <tr>
                                    <td width="8" height="10" class="gp-frame-bl"></td>
                                    <td height="10" class="gp-frame-b"></td>
                                    <td width="8" height="10" class="gp-frame-br"></td>
                                </tr>
                            </table>
                        </td>

                        <td width="10">&nbsp;</td>

                        {{-- Center: upgrade panel --}}
                        <td width="31%">
                            <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                                <tbody>
                                <tr height="22">
                                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                    <td align="center" class="tbl-usi-hdr mbg">Заточка</td>
                                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                </tr>
                                </tbody>
                            </table>

                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="8" height="10" class="gp-frame-tl"></td>
                                    <td height="10" class="gp-frame-t"></td>
                                    <td width="8" height="10" class="gp-frame-tr"></td>
                                </tr>
                                <tr>
                                    <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                    <td class="gp-frame-bg" align="center" id="upgrade-panel-content">
                                        <span style="color:#888;">Выберите предмет</span>
                                    </td>
                                    <td width="8" class="gp-frame-r"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                </tr>
                                <tr>
                                    <td width="8" height="10" class="gp-frame-bl"></td>
                                    <td height="10" class="gp-frame-b"></td>
                                    <td width="8" height="10" class="gp-frame-br"></td>
                                </tr>
                            </table>
                        </td>

                        <td width="10">&nbsp;</td>

                        {{-- Right: scroll lists --}}
                        <td width="31%">
                            {{-- Base scrolls (required) --}}
                            <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                                <tbody>
                                <tr height="22">
                                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                    <td align="center" class="tbl-usi-hdr mbg">Свиток заточки *</td>
                                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                </tr>
                                </tbody>
                            </table>

                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:6px;">
                                <tr>
                                    <td width="8" height="10" class="gp-frame-tl"></td>
                                    <td height="10" class="gp-frame-t"></td>
                                    <td width="8" height="10" class="gp-frame-tr"></td>
                                </tr>
                                <tr>
                                    <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                    <td class="gp-frame-bg">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tbody>
                                            @forelse($baseScrolls as $scroll)
                                                <tr class="scroll-row base-scroll-row"
                                                    data-scroll-id="{{ $scroll['id'] }}"
                                                    onclick="selectBaseScroll({{ $scroll['id'] }}, this)">
                                                    <td width="30" style="padding:3px 4px;">
                                                        <img src="{{ $scroll['image'] }}" width="40" height="40"
                                                             data-id="{{ $scroll['id'] }}"
                                                             onmouseover="showItemInfo(this,event,2)"
                                                             onmouseout="showItemInfo(this,event,0)">
                                                    </td>
                                                    <td style="padding:3px 4px;">
                                                        {{ $scroll['name'] }}
                                                        @if($scroll['count'] > 1)
                                                            <span style="color:#888;">({{ $scroll['count'] }})</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" style="padding:6px; color:#c00; text-align:center;">Нет свитков</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                    <td width="8" class="gp-frame-r"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                </tr>
                                <tr>
                                    <td width="8" height="10" class="gp-frame-bl"></td>
                                    <td height="10" class="gp-frame-b"></td>
                                    <td width="8" height="10" class="gp-frame-br"></td>
                                </tr>
                            </table>

                            {{-- Bonus scrolls (optional) --}}
                            <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                                <tbody>
                                <tr height="22">
                                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                    <td align="center" class="tbl-usi-hdr mbg">Доп. свиток</td>
                                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                </tr>
                                </tbody>
                            </table>

                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td width="8" height="10" class="gp-frame-tl"></td>
                                    <td height="10" class="gp-frame-t"></td>
                                    <td width="8" height="10" class="gp-frame-tr"></td>
                                </tr>
                                <tr>
                                    <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                    <td class="gp-frame-bg">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tbody>
                                            <tr class="bonus-scroll-row" id="bonus-scroll-none-row"
                                                style="display:none;" onclick="selectBonusScroll(null, this)">
                                                <td colspan="2" align="center" style="padding:4px;">
                                                    <span class="butt1 pointer"><span><input value="Без доп. свитка" type="button" class="grnn"></span></span>
                                                </td>
                                            </tr>
                                            @foreach($bonusScrolls as $scroll)
                                                <tr class="scroll-row bonus-scroll-row"
                                                    data-scroll-id="{{ $scroll['id'] }}"
                                                    data-scroll-bonus="{{ $scroll['bonusType'] }}"
                                                    onclick="selectBonusScroll({{ $scroll['id'] }}, this)">
                                                    <td width="30" style="padding:3px 4px;">
                                                        <img src="{{ $scroll['image'] }}" width="40" height="40"
                                                             data-id="{{ $scroll['id'] }}"
                                                             onmouseover="showItemInfo(this,event,2)"
                                                             onmouseout="showItemInfo(this,event,0)">
                                                    </td>
                                                    <td style="padding:3px 4px;">
                                                        {{ $scroll['name'] }}
                                                        @if($scroll['count'] > 1)
                                                            <span style="color:#888;">({{ $scroll['count'] }})</span>
                                                        @endif
                                                        <br><span style="color:#666; font-size:10px;">{{ $scroll['description'] }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                    <td width="8" class="gp-frame-r"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                </tr>
                                <tr>
                                    <td width="8" height="10" class="gp-frame-bl"></td>
                                    <td height="10" class="gp-frame-b"></td>
                                    <td width="8" height="10" class="gp-frame-br"></td>
                                </tr>
                            </table>
                        </td>

                    </tr>
                </table>
            </form>

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
    const upgradeData = @json(collect($items)->keyBy('id'));
    const baseScrollData  = @json(collect($baseScrolls)->keyBy('id'));
    const bonusScrollData = @json(collect($bonusScrolls)->keyBy('id'));

    let selectedItemId        = null;
    let selectedBaseScrollId  = null;
    let selectedBonusScrollId = null;
    let luckyActive           = false;

    document.querySelectorAll('.item-row').forEach(function (row) {
        row.addEventListener('click', function () {
            document.querySelectorAll('.item-row').forEach(r => r.classList.remove('selected'));
            this.classList.add('selected');
            const itemId = parseInt(this.dataset.itemId);
            selectedItemId = itemId;
            document.getElementById('selected-item-id').value = itemId;
            localStorage.setItem('upgrade_selected_item', itemId);
            renderUpgradePanel(itemId);
        });
    });

    // Restore selected base scroll after page reload
    const savedBaseScrollId = parseInt(localStorage.getItem('upgrade_selected_base_scroll') || '0');
    if (savedBaseScrollId) {
        const savedScrollRow = document.querySelector(`.base-scroll-row[data-scroll-id="${savedBaseScrollId}"]`);
        if (savedScrollRow) savedScrollRow.click();
    }

    // Restore selected item after page reload (after scroll so panel shows scroll too)
    const savedItemId = parseInt(localStorage.getItem('upgrade_selected_item') || '0');
    if (savedItemId) {
        const savedRow = document.querySelector(`.item-row[data-item-id="${savedItemId}"]`);
        if (savedRow) savedRow.click();
    }

    function selectBaseScroll(scrollId, el) {
        document.querySelectorAll('.base-scroll-row').forEach(r => r.classList.remove('selected'));
        el.classList.add('selected');
        selectedBaseScrollId = scrollId;
        document.getElementById('selected-base-scroll-id').value = scrollId ?? '';
        localStorage.setItem('upgrade_selected_base_scroll', scrollId ?? '');
        if (selectedItemId) renderUpgradePanel(selectedItemId);
    }

    function selectBonusScroll(scrollId, el) {
        document.querySelectorAll('.bonus-scroll-row').forEach(r => r.classList.remove('selected'));
        el.classList.add('selected');
        selectedBonusScrollId = scrollId;
        document.getElementById('selected-bonus-scroll-id').value = scrollId ?? '';
        document.getElementById('bonus-scroll-none-row').style.display = scrollId ? '' : 'none';
        luckyActive = el.dataset.scrollBonus === 'lucky';
        if (selectedItemId) renderUpgradePanel(selectedItemId);
    }

    function renderUpgradePanel(itemId) {
        const d = upgradeData[itemId];
        if (!d) return;

        const chance = luckyActive ? d.successChanceLucky : d.successChance;
        const chanceClass = chance >= 70 ? '' : (chance >= 40 ? 'medium' : 'low');

        const itemHtml = `
            <span class="upgrade-icon"
                  data-id="${itemId}"
                  onmouseover="showItemInfo(this,event,2)"
                  onmouseout="showItemInfo(this,event,0)">
                <img src="${d.image}" alt="">
            </span><br>
            <b>${d.name}</b><br>
        `;

        let html = '';

        if (d.isMax) {
            html = `${itemHtml}<div class="lvl-max">Максимальный уровень +15<br>Заточка невозможна.</div>`;
        } else {
            const scroll = selectedBaseScrollId ? baseScrollData[selectedBaseScrollId] : null;
            const scrollHtml = scroll
                ? `<img src="${scroll.image}" width="28" height="28" style="vertical-align:middle;margin-right:4px;"><b>${scroll.name}</b>${scroll.count > 1 ? ` <span style="color:#888;">(${scroll.count})</span>` : ''}`
                : `<span style="color:#c00;">— не выбран —</span>`;

            const bonus = selectedBonusScrollId ? bonusScrollData[selectedBonusScrollId] : null;
            const bonusHtml = bonus
                ? `<img src="${bonus.image}" width="28" height="28" style="vertical-align:middle;margin-right:4px;"><b>${bonus.name}</b>${bonus.count > 1 ? ` <span style="color:#888;">(${bonus.count})</span>` : ''}<br><span style="color:#666;font-size:10px;">${bonus.description}</span>`
                : `<span style="color:#888;">— не выбран —</span>`;

            html = `
                ${itemHtml}
                <span>Уровень: <b class="lvl-badge">+${d.level}</b> &rarr; <b class="lvl-badge">+${d.level + 1}</b></span><br><br>
                <span>Шанс успеха: <b>${chance.toFixed(0)}%</b></span><br>
                <div class="upgrade-chance-bar"><div class="upgrade-chance-fill ${chanceClass}" style="width:${Math.min(chance,100)}%"></div></div>
                ${d.destroyChance > 0 ? `<span style="color:#c00;">Шанс уничтожения: <b>${d.destroyChance}%</b></span><br>` : ''}
                ${d.failStreak >= 10 ? `<span style="color:#489200;">⚡ Гарантированный успех!</span><br>` : (d.pity > 0 ? `<span style="color:#888;">Pity: +${d.pity * 2}%</span><br>` : '')}
                <br>
                <span>Стоимость: <img src="{{ asset('img/icon/m_game.gif') }}" width="10" height="10"> <b>${d.cost.toLocaleString()}</b></span><br>
                <div style="margin:6px 0 2px; font-size:11px; color:#666;">Свиток: ${scrollHtml}</div>
                <div style="margin:0 0 4px; font-size:11px; color:#666;">Доп. свиток: ${bonusHtml}</div>
                <div class="upgrade-progress-wrap" id="upgrade-progress-wrap">
                    <div class="upgrade-progress-fill" id="upgrade-progress-fill"></div>
                </div>
                <span class="butt1 pointer">
                    <span><input value="Заточить" type="button" onclick="startUpgrade()" class="grnn"></span>
                </span>
            `;
        }

        document.getElementById('upgrade-panel-content').innerHTML = html;
    }

    function startUpgrade() {
        if (!selectedItemId) return;

        if (!selectedBaseScrollId) {
            window.parent.systemInfo('Для заточки необходимо выбрать свиток заточки.', 'Внимание');
            return;
        }

        const wrap = document.getElementById('upgrade-progress-wrap');
        const fill = document.getElementById('upgrade-progress-fill');
        if (!wrap || !fill) return;

        const btn = document.querySelector('#upgrade-panel-content input[type=button]');
        if (btn) btn.disabled = true;

        wrap.style.display = 'block';
        fill.getBoundingClientRect();
        fill.style.width = '100%';

        setTimeout(function () {
            document.getElementById('upgrade-form').submit();
        }, 1050);
    }

    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }


    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}');

        // Обновляем параметры персонажа в character-frame после заточки
        @php
            $hp         = $player->hp_now;
            $hpMax      = $playerDecorator->getHpMax();
            $mp         = $player->mp_now;
            $mpMax      = $playerDecorator->getMpMax();
            $experience = $player->getPercentExp();
            $lvl        = $player->lvl;
            $money      = $user->money;
            $diamond    = $user->diamond;
        @endphp
        parent.sendToFrame('character-frame', {
            hp:         { current: {{ $hp }}, max: {{ $hpMax }} },
            mp:         { current: {{ $mp }}, max: {{ $mpMax }} },
            experience: {{ $experience }},
            lvl:        {{ $lvl }},
            money:      {{ $money }},
            diamond:    {{ $diamond }},
        });
    @endif
</script>

{!! $playerStatsScript !!}
    {!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>

</body>
</html>
