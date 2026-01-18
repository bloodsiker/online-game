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

                        <table border="0" cellspacing="0" cellpadding="0" style="position: relative; top: -2px;">
                            <tbody>
                            @php
                                $btnLeft1 = 'img/bg/btn/btn-left1.gif';
                                $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
                                $btnRight1 = 'img/bg/btn/btn-right1.gif';

                                $btnLeft2 = 'img/bg/btn/btn-left2.gif';
                                $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
                                $btnRight2 = 'img/bg/btn/btn-right2.gif';
                            @endphp
                            <tr height="21">
                                <td width="19"><img id="left_1" src="{{ asset($group === 'character' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_1" align="center" style="background: url({{ asset($group === 'character' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_1" href="{{ route('character', ['group' => 'character']) }}" title="Персонаж" class="{{ $group === 'character' ? 'btn_2' : 'btn_1' }}">Персонаж</a>
                                </td>
                                <td width="19"><img id="right_1" src="{{ asset($group === 'character' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_2" src="{{ asset($group === 'magic_skill' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_2" align="center" style="background: url({{ asset($group === 'magic_skill' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_2" href="{{ route('magic_skill', ['group' => 'magic_skill']) }}" title="Книга заклинаний" class="{{ $group === 'magic_skill' ? 'btn_2' : 'btn_1' }}">Книга заклинаний</a>
                                </td>
                                <td width="19"><img id="right_2" src="{{ asset($group === 'magic_skill' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_2" src="{{ asset($group === 'slots' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_2" align="center" style="background: url({{ asset($group === 'slots' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_2" href="{{ route('slots', ['group' => 'slots']) }}" title="Слоты" class="{{ $group === 'slots' ? 'btn_2' : 'btn_1' }}">Слоты</a>
                                </td>
                                <td width="19"><img id="right_2" src="{{ asset($group === 'slots' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
                            </tr>
                            </tbody>
                        </table>

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
                                    <div class="user-effects-set bg_l&gt;" data-id="24">
                                        <ul class="lscroll backpack_list connected-sortable clearfix">
                                            <li id="AA_502829" aid="art_502829" sn="0" ord="0" data-id="502829"
                                                data-artikul-id="7188" data-dateti="502829" data-quality="2"
                                                data-kind="34" data-ttl="-1768069414" data-title="Волшебная палочка"
                                                data-noweight="0" data-cnt="0" num="1" class="item" style="opacity: 1;">


                                                <table class="item pctntr " data-id="502829" width="60" height="60"
                                                       cellpadding="0" cellspacing="0" border="0"
                                                       style="float: left; margin: 1px"
                                                       background="https://feo-dwar.com/images/data/artifacts/volpal_1504.gif">
                                                    <tbody>
                                                    <tr>
                                                        <td act1="8" act2="0" act3="0" rune_h="0"
                                                            style="position: relative; background-image: url(&quot;images/d.gif&quot;); cursor: pointer;"
                                                            aid="502829" art_id="" cnt="0" div_id="AA_502829"
                                                            onmouseover="showItemInfo(this,event,2)"
                                                            onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                            &nbsp;

                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </li>

                                            <li class="user-effects-set__add"></li>

                                            <li class="user-effects-set__add"></li>

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
                                                    <script>
                                                        if (!window.jQuery) document.write('<scr' + 'ipt src="js/jquery.js"></scr' + 'ipt>');
                                                    </script>
                                                    <script src="js/jquery.js"></script>
                                                    <script src="js/jquery.itemsfilter.js?c=1322"></script>
                                                    <script type="text/javascript" src="js/json2.js?c=1322"></script>
                                                    <script src="js/jstorages.js?c=1322"></script>
                                                    <script>
                                                        $(function () {
                                                            itemsFilterInit();
                                                            $('#items-filter-search').on('click', function (e) {
                                                                my_menu_search(this, e);
                                                            });
                                                            var filter = $('.backpack_list').itemsFilter('filter_get');
                                                            my_menu_search(gebi('items-filter-search'), null, (filter.filterValue ? {show: true} : {hide: true}));
                                                        });

                                                        function itemsFilterInit(context) {
                                                            $('.backpack_list', context || document).itemsFilter({
                                                                storageKey: ('itemsFilterStore_instapockets_' + _top().myId),
                                                                itemSelector: 'li.item',
                                                                sortField: 'ord',
                                                                sortOrder: 'asc',
                                                                filterElement: '#item_list_filter',
                                                                fields: {
                                                                    'data-kind': {attr: 'data-kind', type: 'i'},
                                                                    'data-title': {attr: 'data-title', type: 's'},
                                                                    'data-quality': {attr: 'data-quality', type: 's'},
                                                                    'data-ttl': {
                                                                        attr: 'data-ttl',
                                                                        type: 'i',
                                                                        zero: true
                                                                    },
                                                                    'data-noweight': {attr: 'data-noweight', type: 'i'},
                                                                    'ord': {attr: 'ord', type: 'i'}
                                                                }
                                                            });
                                                        }

                                                        function itemsFilterSync() {
                                                            $('.backpack_list').itemsFilter('sort');
                                                            $('.backpack_list').itemsFilter('filter');
                                                            var filter = $('.backpack_list').itemsFilter('filter_get');
                                                            my_menu_search(gebi('items-filter-search'), null, (filter.filterValue ? {show: true} : {hide: true}));
                                                        }
                                                    </script>
                                                    <script type="text/javascript" src="js/gmnu.js?c=1322"></script>
                                                    <script>
                                                        function my_menu_search(obj, e, params) {
                                                            params = params || {};
                                                            var menu = [
                                                                {
                                                                    head: true,
                                                                    txt: 'Поиск по названию',
                                                                    nosort: true
                                                                },
                                                                {
                                                                    input: true,
                                                                    txt: 'Введите название предмета',
                                                                    name: 'filterField',
                                                                    value: '',
                                                                    keyup: function () {
                                                                        $('.backpack_list').itemsFilter('filter', {
                                                                            filterField: 'data-title',
                                                                            filterValue: this.value
                                                                        });
                                                                    },
                                                                    clear: function () {
                                                                        $('.backpack_list').itemsFilter('filter', {
                                                                            filterField: 'data-title',
                                                                            filterValue: ''
                                                                        });
                                                                    }
                                                                }
                                                            ];

                                                            var filter = $('.backpack_list').itemsFilter('filter_get');
                                                            menu[1].value = filter.filterValue || '';

                                                        }

                                                        var currentBagGroupId = '0';

                                                        function showBagSettings() {
                                                            var elm = document.getElementById("bag_settings_" + currentBagGroupId);
                                                            if (!elm) {
                                                                return;
                                                            }
                                                            adjustBagSettings(elm);
                                                            (elm.style.display == 'none') ? elm.style.display = "block" : elm.style.display = "none";
                                                        }

                                                        function adjustBagSettings(elm) {
                                                            var elm2 = document.getElementById('items-filter-' + currentBagGroupId);
                                                            if (elm && elm2) {
                                                                elm.style.top = (elm2.offsetHeight || 24) + 6 + 'px';
                                                            }
                                                        }

                                                        $(window).on('resize', function () {
                                                            adjustBagSettings(document.getElementById("bag_settings_" + currentBagGroupId));
                                                        });
                                                    </script>

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
                                                        <li id="AA_502809" aid="art_502809" sn="0" ord="0"
                                                            data-id="502809" data-artikul-id="14015"
                                                            data-dateti="502809" data-quality="2" data-kind="103"
                                                            data-ttl="-1768069414" data-title="Ветер странствий"
                                                            data-noweight="0" data-cnt="0" num="3" class="item"
                                                            style="opacity: 1;">


                                                            <table class="item pctntr " data-id="502809" width="60"
                                                                   height="60" cellpadding="0" cellspacing="0"
                                                                   border="0" style="float: left; margin: 1px"
                                                                   background="https://feo-dwar.com/images/data/artifacts/battlerageelixir.gif">
                                                                <tbody>
                                                                <tr>
                                                                    <td act1="5" act2="0" act3="0" rune_h="0"
                                                                        style="position: relative;" aid="502809"
                                                                        art_id="" cnt="0" div_id="AA_502809"
                                                                        onmouseover="showItemInfo(this,event,2)"
                                                                        onmouseout="showItemInfo(this,event,0)"
                                                                        valign="bottom">
                                                                        &nbsp;

                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </li>
                                                        <li id="AA_502828" aid="art_502828" sn="0" ord="0"
                                                            data-id="502828" data-artikul-id="1146" data-dateti="502828"
                                                            data-quality="1" data-kind="108" data-ttl="-1768069414"
                                                            data-title="Амулет Крионского Зорба" data-noweight="0"
                                                            data-cnt="0" num="3" class="item" style="opacity: 1;">


                                                            <table class="item pctntr " data-id="502828" width="60"
                                                                   height="60" cellpadding="0" cellspacing="0"
                                                                   border="0" style="float: left; margin: 1px"
                                                                   background="https://game.elders.com.ua/img/resource/ancient_coin.jpg">
                                                                <tbody>
                                                                <tr>
                                                                    <td act1="5" act2="0" act3="0" rune_h="0"
                                                                        style="position: relative; background-image: url(&quot;images/d.gif&quot;); cursor: pointer;"
                                                                        aid="502828" art_id="" cnt="0"
                                                                        div_id="AA_502828"
                                                                        onmouseover="showItemInfo(this,event,2)"
                                                                        onmouseout="showItemInfo(this,event,0)"
                                                                        valign="bottom">
                                                                        &nbsp;

                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </li>
                                                        <li id="AA_502829" aid="art_502829" sn="0" ord="0"
                                                            data-id="502829" data-artikul-id="7188" data-dateti="502829"
                                                            data-quality="2" data-kind="34" data-ttl="-1768069414"
                                                            data-title="Волшебная палочка" data-noweight="0"
                                                            data-cnt="0" num="3" class="item unavailable"
                                                            style="opacity: 1;">


                                                            <table class="item pctntr " data-id="502829" width="60"
                                                                   height="60" cellpadding="0" cellspacing="0"
                                                                   border="0" style="float: left; margin: 1px"
                                                                   background="https://feo-dwar.com/images/data/artifacts/NastroenieVBanke.png">
                                                                <tbody>
                                                                <tr>
                                                                    <td act1="5" act2="0" act3="0" rune_h="0"
                                                                        style="position: relative; background-image: url(&quot;images/d.gif&quot;); cursor: pointer;"
                                                                        aid="502829" art_id="" cnt="0"
                                                                        div_id="AA_502829"
                                                                        onmouseover="showItemInfo(this,event,2)"
                                                                        onmouseout="showItemInfo(this,event,0)"
                                                                        valign="bottom">
                                                                        &nbsp;

                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </li>
                                                        <li id="AA_502830" aid="art_502830" sn="0" ord="0"
                                                            data-id="502830" data-artikul-id="7704" data-dateti="502830"
                                                            data-quality="2" data-kind="34" data-ttl="-1768069414"
                                                            data-title="Дракончик Урчи" data-noweight="0" data-cnt="0"
                                                            num="3" class="item" style="opacity: 1;">


                                                            <table class="item pctntr " data-id="502830" width="60"
                                                                   height="60" cellpadding="0" cellspacing="0"
                                                                   border="0" style="float: left; margin: 1px"
                                                                   background="https://feo-dwar.com/images/data/artifacts/lab_panacea_vio2.gif">
                                                                <tbody>
                                                                <tr>
                                                                    <td act1="5" act2="0" act3="0" rune_h="0"
                                                                        style="position: relative; background-image: url(&quot;images/d.gif&quot;); cursor: pointer;"
                                                                        aid="502830" art_id="" cnt="0"
                                                                        div_id="AA_502830"
                                                                        onmouseover="showItemInfo(this,event,2)"
                                                                        onmouseout="showItemInfo(this,event,0)"
                                                                        valign="bottom">
                                                                        &nbsp;

                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </li>

                                                        <li id="AA_513733" aid="art_513733" sn="3" ord="3"
                                                            data-id="513733" data-artikul-id="17156"
                                                            data-dateti="513733" data-quality="3" data-kind="72"
                                                            data-ttl="-1768069414"
                                                            data-title="Драгоценный аппарат обнуления" data-noweight="0"
                                                            data-cnt="12" num="3" class="item" style="opacity: 1;">


                                                            <table class="item pctntr " data-id="513733" width="60"
                                                                   height="60" cellpadding="0" cellspacing="0"
                                                                   border="0" style="float: left; margin: 1px"
                                                                   background="https://feo-dwar.com/images/data/artifacts/q16_baf_nagrada.gif">
                                                                <tbody>
                                                                <tr>
                                                                    <td act1="5" act2="0" act3="0" rune_h="0"
                                                                        style="position: relative; background-image: url(&quot;images/d.gif&quot;); cursor: pointer;"
                                                                        aid="513733" art_id="" cnt="12"
                                                                        div_id="AA_513733"
                                                                        onmouseover="showItemInfo(this,event,2)"
                                                                        onmouseout="showItemInfo(this,event,0)"
                                                                        valign="bottom">
                                                                        <div class="bpdig">
                                                                            12
                                                                        </div>


                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </li>
                                                        <li id="AA_662355" aid="art_662355" sn="5" ord="5"
                                                            data-id="662355" data-artikul-id="19563"
                                                            data-dateti="662355" data-quality="0" data-kind="66"
                                                            data-ttl="1807379" data-title="Пустая радужная сфера"
                                                            data-noweight="0" data-cnt="14" num="3" class="item"
                                                            style="opacity: 1;">


                                                            <table class="item pctntr " data-id="662355" width="60"
                                                                   height="60" cellpadding="0" cellspacing="0"
                                                                   border="0" style="float: left; margin: 1px"
                                                                   background="https://feo-dwar.com/images/data/artifacts/gtm_device_restored.gif">
                                                                <tbody>
                                                                <tr>
                                                                    <td act1="5" act2="0" act3="0" rune_h="0"
                                                                        style="position: relative;" aid="662355"
                                                                        art_id="" cnt="14" div_id="AA_662355"
                                                                        onmouseover="showItemInfo(this,event,2)"
                                                                        onmouseout="showItemInfo(this,event,0)"
                                                                        valign="bottom">
                                                                        <div class="bpdig">
                                                                            14
                                                                        </div>


                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </li>

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

    function saveCombos() {
        const params = {
            skills: []
        };

        const combos = document.querySelectorAll(".combo-in-fight");

        combos.forEach(el => {
            if (el.checked) {
                params.skills.push(el.value);
            }
        });

        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch('{{ route('magic_skill.update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(params)
        })
            .then(response => response.json())
            .then(data => {
                window.parent.showErrorIframe(data.message || 'Сохранено');
            })
            .catch(() => {
                window.parent.showErrorIframe('Ошибка при сохранении');
            });
    }

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
    function hideArtifacts() {
        document.querySelectorAll('.user-effects-set__add')
            .forEach(el => el.classList.remove('user-effects-set-active'));

        const popup = document.getElementById('artifactsPopup');
        if (popup) {
            popup.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const addButtons = document.querySelectorAll('.user-effects-set__add');
        const popup = document.getElementById('artifactsPopup');

        addButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                addButtons.forEach(b =>
                    b.classList.remove('user-effects-set-active')
                );
                btn.classList.add('user-effects-set-active');
                if (popup) {
                    popup.style.display = 'block';
                }
            });
        });
    });
</script>

</body>
</html>
