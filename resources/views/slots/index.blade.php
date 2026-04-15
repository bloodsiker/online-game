<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Персонаж</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        .t0 {
            background: url({{ asset('img/bg/table-header2.jpg') }}) repeat-x top left;
            background-color: #EDD5C3;
        }
        .t1 {
            background: url({{ asset('img/bg/table-header.jpg') }}) repeat-x top left;
            background-color: #DFBBA3;
        }
        .l0 {
            background-color: #FFF8EA;
        }
        .l1 {
            background-color: #FFFBF5;
        }

        .tbgr {
            background-color: #FADCC2;
        }

        .tbl-shp-sides.ls, .tbl-shp-sides_0.ls {
            background-position: left top;
            background-repeat: repeat-y;
        }
        .tbl-shp-sides.rs, .tbl-shp-sides_0.rs {
            background-position: right top;
            background-repeat: repeat-y;
        }
        .tbl-shp-sml.rt, .tbl-shp-sml_0.rt {
            background-position: 0 -25px;
            height: 22px;
        }
        .tbl-shp-sml.tt, .tbl-shp-sml_0.tt {
            background-position: center -50px;
            background-repeat: repeat-x;
            height: 22px;
        }
        .tbl-shp-sml.lt, .tbl-shp-sml_0.lt {
            background-position: 0 0;
            height: 22px;
        }
        .tbl-shp-sml.lb, .tbl-shp-sml_0.lb {
            background-position: 0 -75px;
        }
        .tbl-shp-sml.bb, .tbl-shp-sml_0.bb {
            background-position: center -125px;
            background-repeat: repeat-x;
            height: 18px;
        }
        .tbl-shp-sml.rb, .tbl-shp-sml_0.rb {
            background-position: 0 -100px;
        }
        .tbl-shp-sml {
            background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat;
            font-size: 0;
        }
        .tbl-shp-sides {
            background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat;
            font-size: 0;
        }
        .tbl-usi_bg {
            background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }});
            background-repeat: repeat;
        }
        .btn_1 {
            color: #461c0b !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 11px;
        }
        .btn_2 {
            color: #ffe9ba !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 11px;
        }
    </style>

    <style>
        .pctntr {
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        .user-effects-set {
            clear: both;
            margin-bottom: 5px;
            padding: 7px;
            border: 1px solid #e3b360;
            -moz-border-radius: 4px;
            -webkit-border-radius: 4px;
            border-radius: 4px;
        }
        .user-effects-set .backpack_list {
            overflow: hidden;
        }
        .backpack_list {
            margin: 0;
            padding: 0;
            min-height: 60px;
        }
        .user-effects-set__add {
            width: 58px !important;
            height: 58px !important;
            background: url(/img/icon/ico-plus.png) 50% 50% #edca9b no-repeat;
            border: 1px solid #db9f73;
            cursor: pointer;
            line-height: 58px;
            text-align: center;
        }
        .backpack_list li {
            list-style: none;
            height: 60px;
            width: 60px;
            margin: 1px;
            float: left;
            position: relative;
            zoom: 1;
        }
        .clearfix:after {
            content: ".";
            display: block;
            clear: both;
            visibility: hidden;
            line-height: 0;
            height: 0;
        }
        .user-effects-set__add.user-effects-set-active {
            border: 1px solid #ff0c0c;
            -moz-box-shadow: 0 0 10px rgba(255, 59, 59, .72);
            -webkit-box-shadow: 0 0 10px rgba(255, 59, 59, .72);
            box-shadow: 0 0 10px rgba(255, 59, 59, .72);
        }

        .popup_global_container {
            box-shadow: 3px 3px 3px -1px rgba(0, 0, 0, 0.2);
        }
        .popup-top-left {
            position: relative;
            background: url(/img/bg/item_slot/popup-top-left.png) left top no-repeat;
        }
        .popup-top-right {
            background: url(/img/bg/item_slot/popup-top-right.png) right top no-repeat;
        }
        .popup_global_close_btn {
            position: absolute;
            right: -2px;
            top: -2px;
            width: 33px;
            height: 35px;
            background: url(/img/bg/item_slot/popup-close.png) right top no-repeat;
            cursor: pointer;
        }
        .popup-top-center {
            margin: 0 36px 0 17px;
            background: url(/img/bg/item_slot/popup-top-center.png) left top repeat-x;
        }
        .popup_global_title {
            height: 24px;
            padding-top: 10px;
            color: #f5f4bf;
            font-weight: bold;
            text-align: center;
        }
        .popup-left-center {
            position: relative;
            background: url(/img/bg/item_slot/popup-left-center.png) left top repeat-y;
        }
        .popup-right-center {
            background: url(/img/bg/item_slot/popup-right-center.png) right top repeat-y;
        }
        .popup_global_content {
            overflow: hidden;
            margin: 0 18px;
            background: url(/img/bg/item_slot/popup-main-bg.png) center center;
        }
        .popup-left-bottom {
            background: url(/img/bg/item_slot/popup-left-bottom.png) left bottom no-repeat;
        }
        .popup-right-bottom {
            background: url(/img/bg/item_slot/popup-right-bottom.png) right bottom no-repeat;
        }
        .popup-bottom-center {
            height: 17px;
            margin: 0 18px;
            background: url(/img/bg/item_slot/popup-bottom-center.png) center bottom repeat-x;
        }
        form {
            margin: 0px;
        }
        .b-filter {
            display: inline-block;
            position: relative;
            margin: 0 0 0 5px;
        }
        .b-filter__icon {
            position: absolute;
            z-index: 1;
            top: -1px;
            left: -5px;
            width: 23px;
            height: 23px;
            cursor: pointer;
            border: 0;
            background: url(/img/icon/btn-search.png) no-repeat;
        }
        .b-filter__reset {
            display: none;
            position: absolute;
            z-index: 1;
            top: 2px;
            right: 3px;
            width: 17px;
            height: 17px;
            cursor: pointer;
            border: 0;
            background: url(/img/icon/btn-clear.png) no-repeat;
        }
        .ff__input-wrap {
            display: inline-block;
            width: 150px;
            height: 23px;
            vertical-align: middle;
            background-position: 0 0;
        }
        .ff__input-wrap, .ff__input-wrap-inner {
            background: url(/img/bg/input/input.png) no-repeat;
        }
        .b-filter .ff__input-wrap {
            width: 210px;
        }
        .ff__input-wrap.it_focus {
            background-position: 0 -92px;
        }
        .ff__input-wrap.it_hover {
            background-position: 0 -46px;
        }
        .ff__input-wrap.it_focus .ff__input-wrap-inner {
            background-position: 100% -115px;
        }
        .ff__input-wrap-inner {
            position: relative;
            height: 100%;
            margin: 0 0 0 6px;
            padding: 0 6px 0 0;
            background-position: 100% -23px;
        }
        .ff__input-wrap-input {
            position: relative;
            height: 100%;
            margin: 0 4px;
        }
        .b-filter .ff__input-wrap-input {
            margin: 0 15px 0 21px;
        }
        .ff__input-wrap input, .ff__input-wrap label {
            position: absolute;
            top: 4px;
            left: 0;
            width: 100% !important;
            margin: 0;
            padding: 0;
            font: 11px Tahoma, Arial, sans-serif;
            color: #6c382c;
            border: 0;
            background: none;
            outline: none;
        }
        .ff__input-wrap label {
            z-index: 1;
            overflow: hidden;
            text-align: left;
            text-overflow: ellipsis;
            color: #c09f79;
            cursor: text;
        }


        .backpack_list {
            margin: 0;
            padding: 0;
            min-height: 60px;
        }
        .backpack_list li {
            list-style: none;
            height: 60px;
            width: 60px;
            margin: 1px;
            float: left;
            position: relative;
            zoom: 1;
        }
        .backpack_list li.item {
            zoom: 1;
        }
        .item--filtered {
            filter: grayscale(100%);
            opacity: 0.4;
            pointer-events: none;
        }
        .bpdig {
            border: solid 1px #6f4a24 !important;
            background-color: #6e534c !important;
            width: 32px !important;
            height: 14px !important;
            color: #f6d9a6 !important;
            font-weight: bold !important;
            margin: 2px !important;
            text-align: center !important;
            position: absolute;
            bottom: 1px;
            left: 0px;
        }
    </style>

    <script src="{{ asset('js/item_tooltip.js') }}"></script>
</head>
<body>

<table class="regcolor" cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="left">

                        @include('player::partials.tabs')

                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">

                        <table class="coll w100 h100">
                            <tbody>
                            <tr>
                                <td valign="top" id="user-effects-sets" class="instapockets-set">
                                    <div class="user-effects-set" data-id="hotbar">
                                        <ul class="lscroll backpack_list connected-sortable clearfix" id="hotbar-slot-list">
                                            @foreach($hotbarData['slots'] as $slot)
                                                @if($slot['empty'])
                                                    <li class="user-effects-set__add" data-slot="{{ $slot['slot'] }}"></li>
                                                @else
                                                    <li class="item hotbar-filled-slot" data-slot="{{ $slot['slot'] }}" data-title="{{ $slot['name'] }}" style="position:relative; opacity:1;">
                                                        <table class="item pctntr" width="60" height="60" cellpadding="0" cellspacing="0" border="0"
                                                               style="float:left; margin:1px;"
                                                               @if($slot['image']) background="{{ asset($slot['image']) }}" @endif>
                                                            <tbody><tr><td style="position:relative;" valign="bottom">&nbsp;</td></tr></tbody>
                                                        </table>
                                                        <span class="hotbar-slot-remove" data-slot="{{ $slot['slot'] }}" title="Убрать из панели"
                                                              style="position:absolute;top:0;right:0;background:#8b0000;color:#fff;font-size:10px;cursor:pointer;padding:1px 3px;line-height:1;z-index:10;">×</span>
                                                        <div style="position:absolute;bottom:0;left:0;right:0;text-align:center;font-size:9px;color:#fff;background:rgba(0,0,0,.5);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">{{ $slot['slot'] }}</div>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td valign="top">
                                    <div class="popup_global_container" id="artifactsPopup" style="display: none;">
                                        <div class="popup-top-left">
                                            <div class="popup-top-right">
                                                <div class="popup-top-center">

                                                    <div class="popup_global_title" id="popup_styled_title">Добавление</div>

                                                </div>
                                            </div>
                                            <div class="popup_global_close_btn" onclick="hideArtifacts();"></div>
                                        </div>

                                        <div class="popup-left-center">
                                            <div class="popup-right-center">

                                                <div class="popup_global_content" style="padding: 0 15px 15px;">

                                                    <form id="item_list_filter">
                                                        <div style="padding: 0 0 10px; text-align: center;">
                                                            <!-- filter control -->
                                                            <div class="b-filter">
                                                                <!-- buttons -->
                                                                <span class="b-filter__icon" title="Поиск по названию">&nbsp;</span>
                                                                <span class="b-filter__reset" title="сбросить фильтр">&nbsp;</span>
                                                                <!-- /buttons -->

                                                                <!-- text input -->
                                                                <div class="ff__input-wrap">
                                                                    <div class="ff__input-wrap-inner">
                                                                        <div class="ff__input-wrap-input">
                                                                            <input type="text" id="filterField" name="filterField" value="">
                                                                            <label for="filterField">Введите название предмета</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- /text input -->
                                                            </div>
                                                            <!-- /filter control -->

                                                            &nbsp;
                                                        </div>
                                                    </form>
                                                    <script>
                                                        document.addEventListener('DOMContentLoaded', () => {
                                                            const input = document.getElementById('filterField');
                                                            const label = document.querySelector('label[for="filterField"]');

                                                            if (!input || !label) return;

                                                            const toggleLabel = () => {
                                                                label.style.display = input.value ? 'none' : '';
                                                            };

                                                            input.addEventListener('focus', () => {
                                                                label.style.display = 'none';
                                                            });
                                                            input.addEventListener('input', toggleLabel);
                                                            input.addEventListener('blur', toggleLabel);
                                                        });
                                                    </script>


                                                    <ul id="item_list" data-caller="0" class="lscroll backpack_list connected-sortable clearfix user-effects-set__items">
                                                        @forelse($usableItems as $backpack)
                                                        <li data-id="{{ $backpack->item_id }}"
                                                            data-title="{{ $backpack->item->itemInfo->name }}"
                                                            data-cnt="{{ $backpack->count }}"
                                                            class="item hotbar-pickable"
                                                            style="opacity: 1; cursor: pointer;"
                                                            title="{{ $backpack->item->itemInfo->name }}">
                                                            <table class="item pctntr" width="60" height="60" cellpadding="0" cellspacing="0" border="0"
                                                                   style="float:left; margin:1px;"
                                                                   @if($backpack->item->itemInfo->image) background="{{ asset($backpack->item->itemInfo->image) }}" @endif>
                                                                <tbody><tr>
                                                                    <td style="position:relative;" valign="bottom">
                                                                        @if($backpack->count > 1)
                                                                            <div class="bpdig">{{ $backpack->count }}</div>
                                                                        @endif
                                                                        &nbsp;
                                                                    </td>
                                                                </tr></tbody>
                                                            </table>
                                                        </li>
                                                        @empty
                                                        <li style="padding:10px; color:#666; list-style:none; float:none; width:auto; height:auto;">
                                                            Нет подходящих предметов
                                                        </li>
                                                        @endforelse
                                                    </ul>

                                                </div>

                                            </div>
                                        </div>

                                        <div class="popup-left-bottom">
                                            <div class="popup-right-bottom">
                                                <div class="popup-bottom-center"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>

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
        </td>
    </tr>
    </tbody>
</table>






<script>
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey || event.metaKey || event.altKey) {
            return;
        }

        switch (event.key.toLowerCase()) {
            case 'i':
                window.parent.sendDataToGame('{{ route('backpack') }}');
                break;
            case 'c':
                window.parent.sendDataToGame('{{ route('character') }}');
                break;
            case ' ':
                window.parent.sendDataToGame('{{ route('location') }}');
                break;
            default:
                return;
        }
        event.preventDefault();
    });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('filterField');
        const items = document.querySelectorAll('#item_list li[data-title]');
        const resetBtn = document.querySelector('.b-filter__reset');

        if (!input || !items.length || !resetBtn) return;

        function applyFilter() {
            const query = input.value.trim().toLowerCase();

            resetBtn.style.display = query.length ? 'block' : 'none';

            items.forEach(item => {
                const title = item.dataset.title?.toLowerCase() || '';

                if (!query || title.includes(query)) {
                    item.classList.remove('item--filtered');
                } else {
                    item.classList.add('item--filtered');
                }
            });
        }

        function resetFilter() {
            input.value = '';
            resetBtn.style.display = 'none';

            items.forEach(item => {
                item.classList.remove('item--filtered');
            });

            input.focus();
        }

        input.addEventListener('input', applyFilter);
        resetBtn.addEventListener('click', resetFilter);
    });
</script>

<script>
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let activeSlotNumber = null;

    function hideArtifacts() {
        document.querySelectorAll('.user-effects-set__add')
            .forEach(el => el.classList.remove('user-effects-set-active'));
        const popup = document.getElementById('artifactsPopup');
        if (popup) popup.style.display = 'none';
        activeSlotNumber = null;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const popup = document.getElementById('artifactsPopup');

        // Открытие попапа при клике на пустой слот
        document.getElementById('hotbar-slot-list').addEventListener('click', (e) => {
            const addBtn = e.target.closest('.user-effects-set__add');
            if (addBtn) {
                document.querySelectorAll('.user-effects-set__add')
                    .forEach(b => b.classList.remove('user-effects-set-active'));
                addBtn.classList.add('user-effects-set-active');
                activeSlotNumber = parseInt(addBtn.dataset.slot);
                if (popup) popup.style.display = 'block';
                return;
            }

            // Удаление из слота по кнопке ×
            const removeBtn = e.target.closest('.hotbar-slot-remove');
            if (removeBtn) {
                const slot = parseInt(removeBtn.dataset.slot);
                removeFromSlot(slot, removeBtn.closest('li'));
            }
        });

        // Выбор предмета из попапа
        document.getElementById('item_list').addEventListener('click', (e) => {
            const li = e.target.closest('.hotbar-pickable');
            if (!li || activeSlotNumber === null) return;

            const itemId = parseInt(li.dataset.id);
            assignToSlot(activeSlotNumber, 'item', itemId, li);
        });
    });

    function assignToSlot(slotNumber, entityType, entityId, sourceLi) {
        fetch('{{ route('hotbar.set') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ slot: slotNumber, entity_type: entityType, entity_id: entityId }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success') {
                window.parent?.showErrorIframe(data.message || 'Ошибка');
                return;
            }
            // Обновляем слот в DOM
            const slotEl = document.querySelector(`#hotbar-slot-list [data-slot="${slotNumber}"]`);
            if (!slotEl) return;

            const bg = sourceLi.querySelector('table')?.getAttribute('background') || '';
            const name = sourceLi.dataset.title || '';

            slotEl.className = 'item hotbar-filled-slot';
            slotEl.dataset.title = name;
            slotEl.innerHTML = `
                <table class="item pctntr" width="60" height="60" cellpadding="0" cellspacing="0" border="0"
                       style="float:left;margin:1px;" ${bg ? `background="${bg}"` : ''}>
                    <tbody><tr><td style="position:relative;" valign="bottom">&nbsp;</td></tr></tbody>
                </table>
                <span class="hotbar-slot-remove" data-slot="${slotNumber}" title="Убрать из панели"
                      style="position:absolute;top:0;right:0;background:#8b0000;color:#fff;font-size:10px;cursor:pointer;padding:1px 3px;line-height:1;z-index:10;">×</span>
                <div style="position:absolute;bottom:0;left:0;right:0;text-align:center;font-size:9px;color:#fff;background:rgba(0,0,0,.5);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">${slotNumber}</div>
            `;
            hideArtifacts();
            window.parent?.refreshHotbar?.();
        });
    }

    function removeFromSlot(slotNumber, slotEl) {
        fetch(`{{ url('/hotbar/clear') }}/${slotNumber}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf },
        })
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success') return;
            slotEl.className = 'user-effects-set__add';
            slotEl.innerHTML = '';
            window.parent?.refreshHotbar?.();
        });
    }
</script>

</body>
</html>
