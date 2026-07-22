<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Камни</title>
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

        .item-row, .gem-row { cursor: pointer; border-bottom: 1px solid #e8c899; }
        .item-row:hover, .gem-row:hover { background: #ffefd5; }
        .item-row.selected, .gem-row.selected { background: #ffe0a0; font-weight: bold; }
        .item-row td, .gem-row td { padding: 3px 4px; }

        /* Золотистая пергаментная рамка для панели сокетов (common-corners/tb/lr + common-bg) */
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

        /* Socket visualization */
        .socket-list { display: flex; flex-direction: column; gap: 6px; margin: 8px 0; }
        .socket-slot {
            display: flex; align-items: center; gap: 6px;
            border: 1px solid #c8a060; border-radius: 3px; padding: 4px 6px;
            background: #fdf6ee; cursor: pointer;
        }
        .socket-slot:hover { background: #ffefd5; }
        .socket-slot.active { border-color: #2255aa; background: #e8eeff; }
        .socket-slot.filled { border-color: #559922; background: #f0f8ea; cursor: default; }
        .socket-slot img.gem-img { width: 30px; height: 30px; }
        .socket-empty-icon {
            width: 30px; height: 30px;background: #fff url('/img/bg/empty_slot.gif') center center / cover no-repeat;
        }
        .socket-label { flex: 1; }
        .socket-remove-btn {
            font-size: 10px; color: #993300; cursor: pointer; padding: 1px 4px;
            border: 1px solid #c05030; border-radius: 2px; background: #fff8f0;
        }
        .socket-remove-btn:hover { background: #ffe0d0; }

        .gem-stats-hint { color: #555; font-size: 10px; margin-top: 2px; }

        .flash-success { background: #d4f0c0; border: 1px solid #60a840; color: #2a6010; padding: 6px 10px; margin-bottom: 8px; }
        .flash-fail    { background: #f8dcd0; border: 1px solid #c05030; color: #7a2010; padding: 6px 10px; margin-bottom: 8px; }
    </style>
</head>
<body class="regcolor" leftmargin="0" rightmargin="0">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('blacksmith::_tabs', ['activeTab' => 'gems'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10px 6px;">

            {{-- Flash message --}}
            @if(session('message'))
                @php $flashClass = session('gem_success', false) ? 'flash-success' : 'flash-fail'; @endphp
                <div class="{{ $flashClass }}">{{ session('message') }}</div>
            @endif

            {{-- Coins --}}
            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td align="left" width="33%" nowrap=""><b>Монет:</b>
                        &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Монеты"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->money) }} </b>
                        &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->diamond) }} </b>
                    </td>
                </tr>
                </tbody>
            </table>

            <br>

            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr valign="top">

                    {{-- Left: socketable items --}}
                    <td width="31%">
                        <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Предметы</td>
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
                                        @forelse($items as $slot)
                                            @php $socketCount = $slot['socketCount']; @endphp
                                            <tr class="item-row" data-item-id="{{ $slot['id'] }}"
                                                onclick="selectItem({{ $slot['id'] }})">
                                                <td width="44">
                                                    <img src="{{ $slot['image'] }}" width="40" height="40"
                                                         data-id="{{ $slot['id'] }}"
                                                         onmouseover="showItemInfo(this,event,2)"
                                                         onmouseout="showItemInfo(this,event,0)">
                                                </td>
                                                <td>
                                                    {{ $slot['name'] }}
                                                    @if($slot['upgradeLevel'] > 0)
                                                        <span style="color:#2255aa; font-weight:bold;">+{{ $slot['upgradeLevel'] }}</span>
                                                    @endif
                                                    <br>
                                                    <span style="color:#888;">
                                                        @if($socketCount === 0)
                                                            Нет сокетов
                                                        @else
                                                            {{ $socketCount }} сокет{{ $socketCount === 1 ? '' : ($socketCount < 5 ? 'а' : 'ов') }}
                                                            / {{ $slot['gemsCount'] }} заполнен
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" style="padding:8px; color:#888; text-align:center;">Нет предметов</td></tr>
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

                    {{-- Center: socket panel --}}
                    <td width="31%">
                        <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Сокеты</td>
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
                                <td class="gp-frame-bg" align="center" id="gem-panel-content">
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

                    {{-- Right: gems + socket kits --}}
                    <td width="31%">
                        {{-- Gems inventory --}}
                        <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Камни</td>
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
                                        <tr class="selected" id="gem-none-row" style="display:none;" onclick="selectGem(null, this)">
                                            <td colspan="2" align="center" style="padding:4px;">
                                                <span class="butt1 pointer"><span><input value="Отменить выбор" type="button" class="grnn"></span></span>
                                            </td>
                                        </tr>
                                        @forelse($gems as $slot)
                                            <tr class="gem-row" data-gem-id="{{ $slot['id'] }}"
                                                onclick="selectGem({{ $slot['id'] }}, this)">
                                                <td width="44">
                                                    <img src="{{ $slot['image'] }}" width="40" height="40"
                                                         data-id="{{ $slot['id'] }}"
                                                         onmouseover="showItemInfo(this,event,2)"
                                                         onmouseout="showItemInfo(this,event,0)">
                                                </td>
                                                <td>
                                                    {{ $slot['name'] }}
                                                    @if($slot['count'] > 1)
                                                        <span style="color:#888;">({{ $slot['count'] }})</span>
                                                    @endif
                                                    @php $stats = $slot['stats'] ?? []; @endphp
                                                    @if(count($stats))
                                                        <br>
                                                        @foreach($stats as $st)
                                                            <span class="gem-stats-hint">
                                                                +{{ $st['value'] }}{{ ($st['is_percent'] ?? false) ? '%' : '' }}
                                                                {{ $st['stat'] ?? '' }}
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" style="padding:6px; color:#888; text-align:center;">Нет камней</td></tr>
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

                        {{-- Mounts (оправы) --}}
                        @if(count($mounts) > 0)
                        <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Оправа</td>
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
                                        <tr id="mount-none-row" style="display:none;" onclick="selectMount(null, this)">
                                            <td colspan="2" align="center" style="padding:4px;">
                                                <span class="butt1 pointer"><span><input value="Отменить выбор" type="button" class="grnn"></span></span>
                                            </td>
                                        </tr>
                                        @foreach($mounts as $slot)
                                            <tr class="gem-row" data-mount-id="{{ $slot['id'] }}"
                                                onclick="selectMount({{ $slot['id'] }}, this)">
                                                <td width="44">
                                                    <img src="{{ $slot['image'] }}" width="40" height="40"
                                                         data-id="{{ $slot['id'] }}"
                                                         onmouseover="showItemInfo(this,event,2)"
                                                         onmouseout="showItemInfo(this,event,0)">
                                                </td>
                                                <td>
                                                    {{ $slot['name'] }}
                                                    @if($slot['count'] > 1)
                                                        <span style="color:#888;">({{ $slot['count'] }})</span>
                                                    @endif
                                                    <br>
                                                    <span class="gem-stats-hint">
                                                        {{ $slot['rarity'] }}: {{ $slot['socketMin'] }}-{{ $slot['socketMax'] }} сокет(ов), {{ $slot['openCost'] }} монет
                                                    </span>
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
                        @endif
                    </td>

                </tr>
            </table>

            {{-- Hidden forms --}}
            <form id="insert-form" action="{{ route('blacksmith.gems.insert', ['id' => $blacksmith->id]) }}" method="post" style="display:none;">
                @csrf
                <input type="hidden" name="item_id"      id="fi-item-id">
                <input type="hidden" name="gem_id"       id="fi-gem-id">
                <input type="hidden" name="socket_index" id="fi-socket-index">
            </form>

            <form id="remove-form" action="{{ route('blacksmith.gems.remove', ['id' => $blacksmith->id]) }}" method="post" style="display:none;">
                @csrf
                <input type="hidden" name="item_id"      id="fr-item-id">
                <input type="hidden" name="socket_index" id="fr-socket-index">
            </form>

            <form id="open-socket-form" action="{{ route('blacksmith.gems.open_socket', ['id' => $blacksmith->id]) }}" method="post" style="display:none;">
                @csrf
                <input type="hidden" name="item_id"  id="fs-item-id">
                <input type="hidden" name="mount_id" id="fs-mount-id">
            </form>

            {{-- Подтверждение извлечения камня --}}
            <div id="gem-remove-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:9999; align-items:center; justify-content:center;">
                <div class="popup_global_container" style="width:280px;">
                    <div class="popup-top-left">
                        <div class="popup-top-right">
                            <div class="popup-top-center">
                                <div class="popup_global_title">Извлечение камня</div>
                            </div>
                        </div>
                        <div class="popup_global_close_btn" onclick="closeGemRemoveConfirm()"></div>
                    </div>
                    <div class="popup-left-center">
                        <div class="popup-right-center">
                            <div class="popup_global_content" style="padding:20px; text-align:center;">
                                <div class="redd" style="margin-bottom:14px;">Извлечь камень из сокета? Камень вернётся в рюкзак.</div>
                                <b class="butt1 pointer"><b><input value="Извлечь" type="button" onclick="confirmGemRemove()" class="redd" style="width:100px;"></b></b>
                                &nbsp;
                                <b class="butt1 pointer"><b><input value="Отмена" type="button" onclick="closeGemRemoveConfirm()" style="width:100px;"></b></b>
                            </div>
                        </div>
                    </div>
                    <div class="popup-left-bottom">
                        <div class="popup-right-bottom">
                            <div class="popup-bottom-center"></div>
                        </div>
                    </div>
                </div>
            </div>

        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="20">
        <td class="tbl-shp-sml lb"><b></b></td>
        <td class="tbl-shp-sml bb"></td>
        <td class="tbl-shp-sml rb"><b></b></td>
    </tr>
    </tbody>
</table>

{!! $playerStatsScript !!}
    {!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>

<script>
const ITEMS_DATA  = @json($items);
const GEMS_DATA   = @json($gems);
const MOUNTS_DATA = @json($mounts);
const MAX_SOCKETS = {{ \App\Modules\Structure\Blacksmith\Domain\Services\GemService::MAX_SOCKETS }};

let selectedItemId   = parseInt(localStorage.getItem('gem_selected_item') || '0');
let selectedGemId    = 0;
let selectedMountId  = 0;
let selectedSocketIndex = -1;

function selectItem(itemId) {
    selectedItemId = itemId;
    selectedGemId  = 0;
    selectedSocketIndex = -1;
    localStorage.setItem('gem_selected_item', itemId);

    document.querySelectorAll('.item-row').forEach(r => r.classList.remove('selected'));
    const row = document.querySelector(`.item-row[data-item-id="${itemId}"]`);
    if (row) row.classList.add('selected');

    renderGemPanel();
}

function selectGem(gemId, el) {
    selectedGemId = gemId;
    document.querySelectorAll('.gem-row[data-gem-id], #gem-none-row').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('gem-none-row').style.display = gemId ? '' : 'none';
    renderGemPanel();
}

function selectMount(mountId, el) {
    selectedMountId = mountId;
    document.querySelectorAll('.gem-row[data-mount-id], #mount-none-row').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('mount-none-row').style.display = mountId ? '' : 'none';
    renderGemPanel();
}

function renderGemPanel() {
    const panel = document.getElementById('gem-panel-content');
    const item  = ITEMS_DATA.find(i => i.id === selectedItemId);

    if (!item) {
        panel.innerHTML = '<span style="color:#888;">Выберите предмет</span>';
        return;
    }

    const gem = GEMS_DATA.find(g => g.id === selectedGemId);

    let html = '';

    // Item header
    html += `<div style="margin-bottom:6px;">`;
    html += `<img src="${item.image}" width="50" height="50" style="border:1px solid #c8a060;"><br>`;
    html += `<b>${item.name}</b><br>`;
    html += `<span style="color:#888;">${item.socketCount === 0 ? 'Нет сокетов' : 'Сокетов: ' + item.socketCount}</span>`;
    html += `</div>`;

    // Socket slots
    if (item.socketCount === 0) {
        html += `<div style="color:#888; margin:8px 0;">Нет сокетов</div>`;
    } else {
        html += `<div class="socket-list">`;
        for (let i = 0; i < item.socketCount; i++) {
            const filled = item.gems.find(g => g.socket_index === i);
            if (filled) {
                html += `<div class="socket-slot filled">`;
                html += `<img src="${filled.img}" class="gem-img" title="${filled.name}">`;
                html += `<span class="socket-label">${filled.name}</span>`;
                html += `<span class="socket-remove-btn" onclick="doRemove(${item.id}, ${i})">✕</span>`;
                html += `</div>`;
            } else {
                const isActive = selectedSocketIndex === i;
                html += `<div class="socket-slot${isActive ? ' active' : ''}" onclick="selectSocket(${i})">`;
                html += `<div class="socket-empty-icon"></div>`;
                html += `<span class="socket-label" style="color:#888;">Сокет ${i+1} — пустой</span>`;
                html += `</div>`;
            }
        }
        html += `</div>`;
    }

    // Insert button
    if (item.socketCount > 0) {
        const canInsert = gem && selectedSocketIndex >= 0 && !item.gems.find(g => g.socket_index === selectedSocketIndex);
        if (gem && selectedSocketIndex >= 0) {
            if (canInsert) {
                html += `<div style="margin-top:8px;">`;
                html += `<div style="margin-bottom:4px; color:#333;">`;
                html += `<img src="${gem.image}" width="24" height="24" style="vertical-align:middle; margin-right:4px;">${gem.name} → Сокет ${selectedSocketIndex+1}`;
                html += `</div>`;
                html += `<span class="butt1 pointer"><span><input value="Вставить камень" type="button" onclick="doInsert()" class="grnn"></span></span>`;
                html += `</div>`;
            } else {
                html += `<div style="margin-top:8px; color:#c05030;">Сокет занят</div>`;
            }
        } else if (!gem) {
            html += `<div style="margin-top:8px; color:#888; font-size:10px;">← Выберите камень и сокет</div>`;
        } else {
            html += `<div style="margin-top:8px; color:#888; font-size:10px;">← Выберите сокет</div>`;
        }
    }

    // Mount button — можно ставить повторно поверх существующих сокетов (апгрейд),
    // но это риск: оправа и золото расходуются даже если ролл окажется хуже текущего.
    if (selectedMountId) {
        const mount = MOUNTS_DATA.find(m => m.id === selectedMountId);
        html += `<div style="margin-top:10px; border-top:1px solid #e8c899; padding-top:8px;">`;
        if (mount) {
            html += `<div style="margin-bottom:4px; color:#333;">Оправа «${mount.rarity}»: ${mount.socketMin}-${mount.socketMax} сокет(ов) за ${mount.openCost} монет</div>`;
            if (item.socketCount > 0) {
                html += `<div style="margin-bottom:4px; color:#c05030; font-size:10px;">Уже открыто ${item.socketCount} сокет(ов). Если выпадет меньше — оправа и золото будут потрачены впустую.</div>`;
            }
        }
        html += `<span class="butt1 pointer"><span><input value="${item.socketCount > 0 ? 'Улучшить оправу' : 'Установить оправу'}" type="button" onclick="doOpenSocket()" class="grnn"></span></span>`;
        html += `</div>`;
    }

    panel.innerHTML = html;
}

function selectSocket(index) {
    selectedSocketIndex = index;
    renderGemPanel();
}

function doInsert() {
    if (!selectedItemId || !selectedGemId || selectedSocketIndex < 0) return;
    document.getElementById('fi-item-id').value      = selectedItemId;
    document.getElementById('fi-gem-id').value       = selectedGemId;
    document.getElementById('fi-socket-index').value = selectedSocketIndex;
    document.getElementById('insert-form').submit();
}

let _pendingGemRemove = null;

function doRemove(itemId, socketIndex) {
    _pendingGemRemove = { itemId, socketIndex };
    document.getElementById('gem-remove-overlay').style.display = 'flex';
}

function closeGemRemoveConfirm() {
    _pendingGemRemove = null;
    document.getElementById('gem-remove-overlay').style.display = 'none';
}

function confirmGemRemove() {
    if (!_pendingGemRemove) return;
    document.getElementById('fr-item-id').value      = _pendingGemRemove.itemId;
    document.getElementById('fr-socket-index').value = _pendingGemRemove.socketIndex;
    document.getElementById('remove-form').submit();
}

function doOpenSocket() {
    if (!selectedItemId || !selectedMountId) return;
    document.getElementById('fs-item-id').value   = selectedItemId;
    document.getElementById('fs-mount-id').value  = selectedMountId;
    document.getElementById('open-socket-form').submit();
}

// Restore selection on page load
document.addEventListener('DOMContentLoaded', function () {
    if (selectedItemId) {
        const row = document.querySelector(`.item-row[data-item-id="${selectedItemId}"]`);
        if (row) {
            row.classList.add('selected');
            renderGemPanel();
        } else {
            selectedItemId = 0;
            localStorage.removeItem('gem_selected_item');
        }
    }

    // After insert/remove — refresh character frame
    @if(session('gem_success'))
        if (window.parent && window.parent.sendToFrame) {
            window.parent.sendToFrame('character-frame', @json($hpMp));
        }
    @endif
});
</script>
</body>
</html>
