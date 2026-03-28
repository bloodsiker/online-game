<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Руны</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .brd2, .brd2 td { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .pointer { cursor: pointer; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .regcolor, .regcolor * { color: #955c4a; }

        .item-row, .rune-row { cursor: pointer; border-bottom: 1px solid #e8c899; }
        .item-row:hover, .rune-row:hover { background: #ffefd5; }
        .item-row.selected, .rune-row.selected { background: #ffe0a0; font-weight: bold; }
        .item-row td, .rune-row td { padding: 3px 4px; }

        .rune-panel { padding: 8px; }

        /* Рунные слоты */
        .rune-slot {
            border: 1px solid #c8a060; border-radius: 3px; padding: 6px 8px;
            margin-bottom: 5px; background: #fdf6ee;
        }
        .rune-slot.empty { border-style: dashed; cursor: pointer; }
        .rune-slot.empty:hover { background: #ffefd5; }
        .rune-slot.active-empty { border-color: #2255aa; background: #e8eeff; }
        .rune-slot-header { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
        .rune-slot-title { font-weight: bold; flex: 1; }
        .rune-remove-btn {
            font-size: 10px; color: #993300; cursor: pointer; padding: 1px 4px;
            border: 1px solid #c05030; border-radius: 2px; background: #fff8f0;
        }
        .rune-remove-btn:hover { background: #ffe0d0; }

        /* Статы руны */
        .rune-stat { display: flex; justify-content: space-between; padding: 1px 0; }
        .rune-stat-name { color: #555; }
        .rune-stat-val { font-weight: bold; color: #334; }
        .rune-stat-locked { color: #cc8800; font-size: 10px; cursor: pointer; }
        .rune-passive { margin-top: 4px; padding: 3px 5px; background: #f0e8ff; border: 1px solid #9933cc; border-radius: 2px; color: #550077; font-size: 10px; }

        /* Режим вплавления */
        .mode-tabs { display: flex; gap: 4px; margin: 6px 0; }
        .mode-tab {
            flex: 1; text-align: center; padding: 3px 0; cursor: pointer;
            border: 1px solid #c8a060; border-radius: 2px; background: #fdf6ee;
        }
        .mode-tab.active { background: #ffe0a0; font-weight: bold; border-color: #a07030; }
        .mode-tab.risk { border-color: #c05030; }
        .mode-tab.risk.active { background: #ffe0d0; }
        .mode-desc { font-size: 10px; color: #666; margin-bottom: 6px; line-height: 1.4; }

        /* Переброс */
        .reroll-section { margin-top: 6px; padding-top: 6px; border-top: 1px solid #e8c899; }
        .reroll-cost { color: #884400; font-weight: bold; }

        .flash-success { background: #d4f0c0; border: 1px solid #60a840; color: #2a6010; padding: 6px 10px; margin-bottom: 8px; }
        .flash-fail    { background: #f8dcd0; border: 1px solid #c05030; color: #7a2010; padding: 6px 10px; margin-bottom: 8px; }

        .rarity-common    { color: #888888; font-weight: bold; }
        .rarity-rare      { color: #2266cc; font-weight: bold; }
        .rarity-epic      { color: #9933cc; font-weight: bold; }
        .rarity-legendary { color: #cc7700; font-weight: bold; }
    </style>
</head>
<body class="regcolor" leftmargin="0" rightmargin="0">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tbody>
<tr height="22">
    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
    <td class="tbl-shp-sml tt" valign="top" align="left">
        @include('blacksmith._tabs', ['activeTab' => 'runes'])
    </td>
    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
</tr>
<tr>
    <td class="tbl-shp-sides ls">&nbsp;</td>
    <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10px 6px;">

        @if(session('message'))
            @php $flashClass = session('rune_success', false) ? 'flash-success' : 'flash-fail'; @endphp
            <div class="{{ $flashClass }}">{{ session('message') }}</div>
        @endif

        <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
        <tbody><tr class="bg_l">
            <td align="left" width="33%" nowrap=""><b>Монет:</b>
                &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Монеты"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->money) }} </b>
                &nbsp;&nbsp;&nbsp;<b class="redd"><span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->diamond) }} </b>
            </td>
        </tr></tbody>
        </table>

        <br>

        @php
            $itemsData = $items->map(fn($slot) => [
                'id'             => $slot->item->id,
                'name'           => $slot->item->itemInfo->name,
                'img'            => $slot->item->itemInfo->image,
                'rune_slot_count'=> $slot->item->rune_slot_count,
                'runes'          => $slot->item->runes->map(fn($r) => [
                    'slot_index'    => $r->slot_index,
                    'share_item_id' => $r->share_item_id,
                    'name'          => $r->runeInfo->name,
                    'img'           => $r->runeInfo->image,
                    'rarity'        => $r->runeInfo->rune_rarity?->value ?? 'common',
                    'rarity_label'  => $r->runeInfo->rune_rarity?->label() ?? '',
                    'stats'         => $r->stats,
                    'passive_skill' => $r->passive_skill,
                    'reroll_count'  => $r->reroll_count,
                    'reroll_cost'   => app(\App\Services\RuneService::class)->nextRerollCost($r, 0),
                ])->values()->toArray(),
            ])->values()->toArray();

            $runesData   = $runes->map(fn($slot) => [
                'id'     => $slot->item->id,
                'name'   => $slot->item->itemInfo->name,
                'img'    => $slot->item->itemInfo->image,
                'count'  => $slot->count,
                'rarity' => $slot->item->itemInfo->rune_rarity?->value ?? 'common',
                'rarity_label' => $slot->item->itemInfo->rune_rarity?->label() ?? '',
            ])->values()->toArray();

            $keysData = $runeKeys->map(fn($slot) => [
                'id'    => $slot->item->id,
                'name'  => $slot->item->itemInfo->name,
                'img'   => $slot->item->itemInfo->image,
                'count' => $slot->count,
            ])->values()->toArray();
        @endphp

        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr valign="top">

            {{-- Левая колонка: предметы --}}
            <td width="33%">
                <table class="coll brd2-all" width="100%" border="0">
                    <thead><tr class="bg_l" height="17">
                        <td class="p6h brd2" colspan="2" align="center"><b>Оружие и щиты</b></td>
                    </tr></thead>
                    <tbody>
                    @forelse($items as $slot)
                        <tr class="item-row" data-item-id="{{ $slot->item->id }}" onclick="selectItem({{ $slot->item->id }})">
                            <td width="44">
                                <img src="{{ $slot->item->itemInfo->image }}" width="40" height="40"
                                     data-id="{{ $slot->item->id }}"
                                     onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)">
                            </td>
                            <td>
                                {{ $slot->item->itemInfo->name }}
                                @if($slot->item->upgrade_lvl > 0)
                                    <span style="color:#2255aa; font-weight:bold;">+{{ $slot->item->upgrade_lvl }}</span>
                                @endif
                                <br>
                                <span style="color:#888;">
                                    @php $sc = $slot->item->rune_slot_count; @endphp
                                    @if($sc === 0) Нет слотов
                                    @else {{ $sc }} слот{{ $sc === 1 ? '' : ($sc < 5 ? 'а' : 'ов') }} / {{ $slot->item->runes->count() }} занят
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="padding:8px; color:#888; text-align:center;">Нет оружия / щитов</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </td>

            <td width="8">&nbsp;</td>

            {{-- Центр: рунная панель --}}
            <td width="34%">
                <table class="coll brd2-all" width="100%" border="0">
                    <thead><tr class="bg_l" height="17">
                        <td class="p6h brd2" align="center"><b>Вплавление рун</b></td>
                    </tr></thead>
                    <tbody><tr>
                        <td class="rune-panel" id="rune-panel-content">
                            <span style="color:#888;">Выберите предмет</span>
                        </td>
                    </tr></tbody>
                </table>
            </td>

            <td width="8">&nbsp;</td>

            {{-- Правая колонка: руны + ключи --}}
            <td width="25%">
                <table class="coll brd2-all" width="100%" border="0" style="margin-bottom:6px;">
                    <thead><tr class="bg_l" height="17">
                        <td class="p6h brd2" colspan="2" align="center"><b>Руны</b></td>
                    </tr></thead>
                    <tbody>
                    <tr class="rune-row selected" id="rune-none-row" onclick="selectRune(null, this)">
                        <td colspan="2" style="color:#888;">— без руны —</td>
                    </tr>
                    @forelse($runes as $slot)
                        @php $rar = $slot->item->itemInfo->rune_rarity; @endphp
                        <tr class="rune-row" data-rune-id="{{ $slot->item->id }}" onclick="selectRune({{ $slot->item->id }}, this)">
                            <td width="44">
                                <img src="{{ $slot->item->itemInfo->image }}" width="40" height="40"
                                     data-id="{{ $slot->item->id }}"
                                     onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)">
                            </td>
                            <td>
                                <span class="rarity-{{ $rar?->value ?? 'common' }}">{{ $rar?->label() }}</span><br>
                                {{ $slot->item->itemInfo->name }}
                                @if($slot->count > 1)
                                    <span style="color:#888;">({{ $slot->count }})</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="padding:6px; color:#888; text-align:center;">Нет рун</td></tr>
                    @endforelse
                    </tbody>
                </table>

                <table class="coll brd2-all" width="100%" border="0">
                    <thead><tr class="bg_l" height="17">
                        <td class="p6h brd2" colspan="2" align="center"><b>Рунные ключи</b></td>
                    </tr></thead>
                    <tbody>
                    <tr class="rune-row selected" id="key-none-row" onclick="selectKey(null, this)">
                        <td colspan="2" style="color:#888;">— без ключа —</td>
                    </tr>
                    @forelse($runeKeys as $slot)
                        <tr class="rune-row" data-key-id="{{ $slot->item->id }}" onclick="selectKey({{ $slot->item->id }}, this)">
                            <td width="44">
                                <img src="{{ $slot->item->itemInfo->image }}" width="40" height="40"
                                     data-id="{{ $slot->item->id }}"
                                     onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)">
                            </td>
                            <td>
                                {{ $slot->item->itemInfo->name }}
                                @if($slot->count > 1)
                                    <span style="color:#888;">({{ $slot->count }})</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="padding:6px; color:#888; text-align:center;">Нет ключей</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </td>

        </tr>
        </table>

        {{-- Скрытые формы --}}
        <form id="imbue-form" action="{{ route('blacksmith.runes.imbue', ['id' => $blacksmith->id]) }}" method="post" style="display:none;">
            @csrf
            <input type="hidden" name="item_id"    id="fi-item-id">
            <input type="hidden" name="rune_id"    id="fi-rune-id">
            <input type="hidden" name="slot_index" id="fi-slot-index">
            <input type="hidden" name="risk_mode"  id="fi-risk-mode" value="0">
        </form>

        <form id="remove-form" action="{{ route('blacksmith.runes.remove', ['id' => $blacksmith->id]) }}" method="post" style="display:none;">
            @csrf
            <input type="hidden" name="item_id"    id="fr-item-id">
            <input type="hidden" name="slot_index" id="fr-slot-index">
        </form>

        <form id="reroll-form" action="{{ route('blacksmith.runes.reroll', ['id' => $blacksmith->id]) }}" method="post" style="display:none;">
            @csrf
            <input type="hidden" name="item_id"    id="frr-item-id">
            <input type="hidden" name="slot_index" id="frr-slot-index">
            <div id="frr-locked-inputs"></div>
        </form>

        <form id="open-slot-form" action="{{ route('blacksmith.runes.open_slot', ['id' => $blacksmith->id]) }}" method="post" style="display:none;">
            @csrf
            <input type="hidden" name="item_id" id="fs-item-id">
            <input type="hidden" name="key_id"  id="fs-key-id">
        </form>

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

{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}"></script>

<script>
const ITEMS_DATA = @json($itemsData);
const RUNES_DATA = @json($runesData);
const KEYS_DATA  = @json($keysData);
const MAX_SLOTS  = {{ \App\Services\RuneService::MAX_RUNE_SLOTS }};

const STAT_LABELS = {
    attack: 'Атака', armor: 'Защита', hp_max: 'Макс. HP', mp_max: 'Макс. MP',
    strength: 'Сила', agility: 'Ловкость', intelligence: 'Интеллект',
    critical: 'Крит. удар', dodge: 'Уклонение',
    left_min_dmg: 'Мин. урон (Л)', left_max_dmg: 'Макс. урон (Л)',
    right_min_dmg: 'Мин. урон (П)', right_max_dmg: 'Макс. урон (П)',
};

const RARITY_CLASS = {
    common: 'rarity-common', rare: 'rarity-rare',
    epic: 'rarity-epic', legendary: 'rarity-legendary',
};

const MODE_INFO = {
    safe: 'Гарантированное вплавление. Статы случайные.',
    risk: 'Шанс провала — руна уничтожается. При успехе более высокие значения статов.',
};

let selectedItemId  = parseInt(localStorage.getItem('rune_selected_item') || '0');
let selectedRuneId  = 0;
let selectedKeyId   = 0;
let selectedSlotIdx = -1;
let imbueMode       = 'safe'; // 'safe' | 'risk'
// lockedIndices per slot: {slot_index: Set<statIndex>}
const lockedStats   = {};

function selectItem(itemId) {
    selectedItemId  = itemId;
    selectedSlotIdx = -1;
    localStorage.setItem('rune_selected_item', itemId);
    document.querySelectorAll('.item-row').forEach(r => r.classList.remove('selected'));
    const row = document.querySelector(`.item-row[data-item-id="${itemId}"]`);
    if (row) row.classList.add('selected');
    renderPanel();
}

function selectRune(runeId, el) {
    selectedRuneId = runeId;
    document.querySelectorAll('.rune-row[data-rune-id], #rune-none-row').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');
    renderPanel();
}

function selectKey(keyId, el) {
    selectedKeyId = keyId;
    document.querySelectorAll('.rune-row[data-key-id], #key-none-row').forEach(r => r.classList.remove('selected'));
    el.classList.add('selected');
    renderPanel();
}

function selectSlot(idx) {
    selectedSlotIdx = idx;
    renderPanel();
}

function setMode(mode) {
    imbueMode = mode;
    renderPanel();
}

function toggleLock(slotIdx, statIdx) {
    if (!lockedStats[slotIdx]) lockedStats[slotIdx] = new Set();
    const set = lockedStats[slotIdx];
    set.has(statIdx) ? set.delete(statIdx) : set.add(statIdx);
    renderPanel();
}

function renderPanel() {
    const panel = document.getElementById('rune-panel-content');
    const item  = ITEMS_DATA.find(i => i.id === selectedItemId);
    if (!item) { panel.innerHTML = '<span style="color:#888;">Выберите предмет</span>'; return; }

    const rune = RUNES_DATA.find(r => r.id === selectedRuneId);
    let html = '';

    html += `<div style="text-align:center; margin-bottom:6px;">`;
    html += `<img src="${item.img}" width="44" height="44" style="border:1px solid #c8a060;"><br>`;
    html += `<b>${item.name}</b><br>`;
    html += `<span style="color:#888;">Рунных слотов: ${item.rune_slot_count}/${MAX_SLOTS}</span>`;
    html += `</div>`;

    // Рунные слоты
    for (let i = 0; i < item.rune_slot_count; i++) {
        const filled = item.runes.find(r => r.slot_index === i);
        if (filled) {
            html += renderFilledSlot(item, filled, i);
        } else {
            const isActive = selectedSlotIdx === i;
            html += `<div class="rune-slot empty${isActive ? ' active-empty' : ''}" onclick="selectSlot(${i})">`;
            html += `<div style="color:#888; text-align:center; padding:4px 0;">◇ Слот ${i+1} — пустой. Нажмите для выбора</div>`;
            html += `</div>`;
        }
    }

    if (item.rune_slot_count === 0) {
        html += `<div style="color:#888; text-align:center; padding:8px;">Нет рунных слотов</div>`;
    }

    // Вплавление: показываем если выбрана пустая ячейка и руна
    if (selectedSlotIdx >= 0 && selectedSlotIdx < item.rune_slot_count) {
        const slotFilled = item.runes.find(r => r.slot_index === selectedSlotIdx);
        if (!slotFilled && rune) {
            html += renderImbueSection(item, rune);
        }
    }

    // Открытие слота
    if (item.rune_slot_count < MAX_SLOTS && selectedKeyId) {
        html += `<div class="reroll-section" style="text-align:center;">`;
        html += `<span class="butt1 pointer"><span><input value="Открыть рунный слот" type="button" onclick="doOpenSlot()" class="grnn"></span></span>`;
        html += `</div>`;
    }

    panel.innerHTML = html;
}

function renderFilledSlot(item, filled, i) {
    const rarClass = RARITY_CLASS[filled.rarity] || 'rarity-common';
    const locked   = lockedStats[i] || new Set();

    let html = `<div class="rune-slot">`;
    html += `<div class="rune-slot-header">`;
    html += `<img src="${filled.img}" width="24" height="24">`;
    html += `<span class="rune-slot-title"><span class="${rarClass}">${filled.rarity_label}</span> ${filled.name}</span>`;
    html += `<span class="rune-remove-btn" onclick="doRemove(${item.id}, ${i})">✕</span>`;
    html += `</div>`;

    // Статы
    filled.stats.forEach((stat, si) => {
        const isLocked = locked.has(si);
        const label    = STAT_LABELS[stat.type] || stat.type;
        html += `<div class="rune-stat">`;
        html += `<span class="rune-stat-name">${label}</span>`;
        html += `<span>`;
        html += `<span class="rune-stat-val">+${stat.value}${stat.is_percent ? '%' : ''}</span>&nbsp;`;
        html += `<span class="rune-stat-locked" title="${isLocked ? 'Снять блокировку' : 'Заблокировать'}" onclick="toggleLock(${i},${si})">${isLocked ? '🔒' : '🔓'}</span>`;
        html += `</span></div>`;
    });

    // Пассивка
    if (filled.passive_skill) {
        html += `<div class="rune-passive">✦ ${filled.passive_skill.description}</div>`;
    }

    // Переброс
    const lockedCount = locked.size;
    const cost = computeRerollCost(filled, lockedCount);
    html += `<div class="reroll-section">`;
    html += `<span class="reroll-cost">Переброс: ${cost} золота</span>`;
    if (lockedCount > 0) html += ` <span style="color:#888;">(${lockedCount} заблок.)</span>`;
    html += `&nbsp;&nbsp;<span class="butt1 pointer"><span><input value="Перебросить" type="button" onclick="doReroll(${item.id},${i})" class="grnn"></span></span>`;
    html += `</div>`;

    html += `</div>`;
    return html;
}

function renderImbueSection(item, rune) {
    let html = `<div style="margin-top:8px; border-top:1px solid #e8c899; padding-top:8px;">`;
    html += `<div style="margin-bottom:4px;">`;
    html += `<img src="${rune.img}" width="24" height="24" style="vertical-align:middle; margin-right:4px;">`;
    html += `<span class="${RARITY_CLASS[rune.rarity]}">${rune.rarity_label}</span> ${rune.name} → Слот ${selectedSlotIdx+1}`;
    html += `</div>`;

    // Переключатель режима
    html += `<div class="mode-tabs">`;
    html += `<div class="mode-tab${imbueMode==='safe' ? ' active' : ''}" onclick="setMode('safe')">Безопасный</div>`;
    html += `<div class="mode-tab risk${imbueMode==='risk' ? ' active' : ''}" onclick="setMode('risk')">Риск</div>`;
    html += `</div>`;
    html += `<div class="mode-desc">${MODE_INFO[imbueMode]}</div>`;

    html += `<span class="butt1 pointer"><span><input value="Вплавить руну" type="button" onclick="doImbue()" class="grnn"></span></span>`;
    html += `</div>`;
    return html;
}

function computeRerollCost(filled, lockedCount) {
    const baseCosts = { common: 100, rare: 300, epic: 800, legendary: 2000 };
    const base = baseCosts[filled.rarity] || 100;
    const cost = Math.round(base * Math.pow(filled.reroll_count + 1, 1.5));
    return cost + lockedCount * Math.round(base * 0.5);
}

function doImbue() {
    document.getElementById('fi-item-id').value    = selectedItemId;
    document.getElementById('fi-rune-id').value    = selectedRuneId;
    document.getElementById('fi-slot-index').value = selectedSlotIdx;
    document.getElementById('fi-risk-mode').value  = imbueMode === 'risk' ? '1' : '0';
    document.getElementById('imbue-form').submit();
}

function doRemove(itemId, slotIdx) {
    document.getElementById('fr-item-id').value    = itemId;
    document.getElementById('fr-slot-index').value = slotIdx;
    document.getElementById('remove-form').submit();
}

function doReroll(itemId, slotIdx) {
    document.getElementById('frr-item-id').value    = itemId;
    document.getElementById('frr-slot-index').value = slotIdx;
    const container = document.getElementById('frr-locked-inputs');
    container.innerHTML = '';
    const locked = lockedStats[slotIdx] || new Set();
    locked.forEach(idx => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'locked_indices[]';
        inp.value = idx;
        container.appendChild(inp);
    });
    document.getElementById('reroll-form').submit();
}

function doOpenSlot() {
    document.getElementById('fs-item-id').value = selectedItemId;
    document.getElementById('fs-key-id').value  = selectedKeyId;
    document.getElementById('open-slot-form').submit();
}

document.addEventListener('DOMContentLoaded', function () {
    if (selectedItemId) {
        const row = document.querySelector(`.item-row[data-item-id="${selectedItemId}"]`);
        if (row) { row.classList.add('selected'); renderPanel(); }
        else { selectedItemId = 0; localStorage.removeItem('rune_selected_item'); }
    }

    @if(session('rune_success'))
        if (window.parent && window.parent.sendToFrame) {
            window.parent.sendToFrame('character-frame', {});
        }
    @endif
});
</script>
</body>
</html>
