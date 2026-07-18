<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Игра</title>
    <style>
        *{
            font-size: 11px;
        }
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 11px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        .hero-itm {
            padding: 1px;
            width: 50px;
            height: 50px;
            border: 0px;
        }
        td.itm {
            padding: 0px 10px 0px 0px;
            white-space: nowrap;
        }
        img.itm {
            width: 50px;
            height: 50px;
            border: 0px;
            margin: 0px 10px 0px 0px;
            padding: 0px;
            vertical-align: middle;
            border-right: 1px solid #CEBBAA;
        }
        .border {
            background-color: #CEBBAA;
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



        .regcolor, .regcolor * {
            color: #955c4a;
        }
        .redd, .redd * {
            color: #BA0000 !important;
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
        .item-put-off {
            border: 0;
            font-size: 12px;
            position: absolute;
            padding: 0;
            top: 18px;
            left: -5px;
            text-align: center;
            background: url({{ asset('img/bg/backpack/slot_button.png') }}) center center / 100% 100% no-repeat;
            width: 60px;
            height: 13px;
            line-height: 13px;
            text-decoration: none;
            display: none;
        }
        .item-put-off:hover {
            filter: brightness(1.08);
        }
        td.item-hero {
            position: relative
        }
        td.item-hero:hover .item-put-off {
            display: block;
        }
    </style>

    {!! $playerStatsScript !!}
    {!! $itemTooltipScript !!}

    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
    <script src="{{ asset('js/lib/Sortable.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
</head>
<body class="regcolor">

<table cellspacing="0" cellpadding="10" width="100%" height="100%" id="item_list">
    <tbody>
    <tr valign="top">
        <td>
            <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">

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
                                <td width="19"><img id="left_1" src="{{ asset($data->getGroup() === 'main' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_1" align="center" style="background: url({{ asset($data->getGroup() === 'main' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_1" href="{{ route('backpack.bag', ['group' => 'main']) }}" title="Доспехи, инструменты, амулеты и прочие полезные вещи" class="{{ $data->getGroup() === 'main' ? 'btn_2' : 'btn_1' }}">Вещи</a>
                                </td>
                                <td width="19"><img id="right_1" src="{{ asset($data->getGroup() === 'main' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_2" src="{{ asset($data->getGroup() === 'key' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_2" align="center" style="background: url({{ asset($data->getGroup() === 'key' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_2" href="{{ route('backpack.bag', ['group' => 'key']) }}" title="Квестовые предметы" class="{{ $data->getGroup() === 'key' ? 'btn_2' : 'btn_1' }}">Ключи</a></td>
                                <td width="19"><img id="right_2" src="{{ asset($data->getGroup() === 'key' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_2" src="{{ asset($data->getGroup() === 'quest' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_2" align="center" style="background: url({{ asset($data->getGroup() === 'quest' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_2" href="{{ route('backpack.bag', ['group' => 'quest']) }}" title="Квестовые предметы" class="{{ $data->getGroup() === 'quest' ? 'btn_2' : 'btn_1' }}">Квесты</a></td>
                                <td width="19"><img id="right_2" src="{{ asset($data->getGroup() === 'quest' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_3" src="{{ asset($data->getGroup() === 'artifact' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_3" align="center" style="background: url({{ asset($data->getGroup() === 'artifact' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_3" href="{{ route('backpack.bag', ['group' => 'artifact']) }}" title="Артефакты" class="{{ $data->getGroup() === 'artifact' ? 'btn_2' : 'btn_1' }}">Артефакты</a></td>
                                <td width="19"><img id="right_3" src="{{ asset($data->getGroup() === 'artifact' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img id="left_4" src="{{ asset($data->getGroup() === 'gift' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td id="tab_4" align="center" style="background: url({{ asset($data->getGroup() === 'gift' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a id="center_4" href="{{ route('backpack.bag', ['group' => 'gift']) }}" title="Подаренные Вам подарки" class="{{ $data->getGroup() === 'gift' ? 'btn_2' : 'btn_1' }}">Подарки</a></td>
                                <td width="19"><img id="right_4" src="{{ asset($data->getGroup() === 'gift' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
                            </tr>
                            </tbody>
                        </table>

                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 6px 4px 6px 4px ">

                        <style>
                            table.coll {
                                border-collapse: collapse;
                                border-spacing: 0;
                            }
                            .p10h, .p10h td {
                                padding-left: 10px;
                                padding-right: 10px;
                            }
                            .brd2-all {
                                border: 1px solid #DB9F73;
                            }
                            .w100 {
                                width: 100%;
                            }
                            .p2v, .p2v td {
                                padding-top: 2px;
                                padding-bottom: 2px;
                            }
                            .bg_l {
                                background-image: url(/img/bg/bg_l.gif);
                            }
                            .lscroll {
                                scrollbar-3dlight-color: #E2D9C3;
                                scrollbar-arrow-color: #760000;
                                scrollbar-base-color: #FFE2A8;
                                scrollbar-darkshadow-color: #4F3224;
                                scrollbar-face-color: #FFE2A8;
                                scrollbar-highlight-color: #FFFFFF;
                                scrollbar-shadow-color: #CF805F;
                                scrollbar-track-color: #FFFBD6;
                            }
                            .backpack_overflow {
                                height: 100%;
                                overflow: scroll;
                                overflow-x: auto;
                                overflow-y: auto;
                                position: relative;
                                z-index: 10;
                            }
                            .backpack_list {
                                margin: 0;
                                padding: 0;
                                min-height: 60px;
                            }
                            .backpack_list li {
                                list-style: none;
                                height: 50px;
                                width: 50px;
                                margin: 1px;
                                float: left;
                                position: relative;
                                zoom: 1;
                            }
                            .backpack_list li.item {
                                zoom: 1;
                                cursor: pointer;
                            }
                            .backpack_list li.empty {
                                clear: both;
                                float: none;
                                text-align: center;
                                width: auto;
                                height: auto;
                                padding: 5px 0;
                            }
                            .bpdig {
                                border: solid 1px #6f4a24 !important;
                                background-color: #6e534c !important;
                                width: 34px !important;
                                height: 12px !important;
                                color: #f6d9a6 !important;
                                font-weight: bold !important;
                                margin: 2px !important;
                                text-align: center !important;
                                font-size: 10px;
                            }
                            .pctntr {
                                background-position: center;
                                background-repeat: no-repeat;
                            }

                            .tbl-usi-hdr {
                                background: url(/img/bg/tbl-usi-hdr.gif) no-repeat;
                                font-family: tahoma, sans-serif;
                                height: 22px;
                            }
                            .tbl-usi-hdr.lc {
                                background-position: left -25px;
                                width: 27px;
                            }
                            .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b {
                                display: block;
                                height: 22px;
                                font-size: 0;
                                overflow: hidden;
                                width: 27px;
                            }
                            .tbl-usi-hdr.mbg {
                                background-position: center -50px;
                                background-repeat: repeat-x;
                                color: #FCF5B7;
                                font-size: 11px;
                                font-weight: bold;
                                height: 16px;
                                padding: 1px 10px 5px 10px;
                                line-height: 16px;
                                vertical-align: middle;
                            }
                            .tbl-usi-hdr.rc {
                                background-position: right 0;
                                width: 27px;
                            }
                        </style>


                        <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                            <tbody>
                            <tr height="100%">
                                <td class="" align="left" valign="top">
                                    <script>
                                        var ask_amount_title = null;

                                        function ask_amount(func, mx, title, id, action) {
                                            ask_amount_title = ask_amount_title || $('#ask_amount_title');
                                            var div = gebi('cart_amount_div');
                                            if (!func) {
                                                div.style.display = 'none';
                                                frame_content_hider.hide();
                                                return;
                                            }

                                            try {
                                                if (no_spros_drop_artifacts_adv !== undefined && no_spros_drop_artifacts_adv === true) { //Кидать максимум не спрашивать хули
                                                    func(mx || 1, id);
                                                    return false;
                                                }
                                            } catch (e) {
                                            }

                                            gebi('cart_amount').value = 1;
                                            var cart_amount_form = gebi('cart_amount_form');
                                            var onsubmit = function () {
                                                onsubmit = function () {
                                                };
                                                cart_amount_form.onsubmit = function () {
                                                    return false;
                                                };
                                                func(gebi('cart_amount').value, id);
                                                return false;
                                            };

                                            var input = $('#cart_amount');
                                            var max_val = parseInt(mx);
                                            if (max_val) {
                                                input.data('min-value', 1);
                                                input.data('max-value', max_val);
                                            } else {
                                                $('#cart_amount_sell_cnt').text('');
                                                input.data('max-value', 1);
                                                input.data('min-value', 1);
                                            }
                                            input[0].onkeyup = counter_controller.keypress;
                                            counter_controller.change(input);

                                            $(document).unbind('keyup');
                                            $(document).on('keyup', function (e) {
                                                if (e.keyCode == 13) { // Enter
                                                    $(this).unbind('keyup');
                                                    onsubmit();
                                                } else if (e.keyCode == 27) { // Esc
                                                    $(this).unbind('keyup');
                                                    ask_amount();
                                                }
                                            });

                                            cart_amount_form.onsubmit = onsubmit;
                                            gebi('cart_amount_all').onclick = function () {
                                                this.onclick = function () {
                                                };
                                                gebi('cart_amount').value = mx;
                                                counter_controller.change(input);
                                            };

                                            div.style.display = 'block';
                                            div.style.top = document.body.scrollTop + (document.body.clientHeight - div.offsetHeight) / 2;
                                            div.style.left = document.body.scrollLeft + (document.body.clientWidth - div.offsetWidth) / 2;

                                            gebi('cart_amount').focus();

                                            if (id) {
                                                var artifact_alt = get_art_alt('AA_' + id);
                                                var title = artifact_alt.title || '';
                                                var artifact_color = artifact_alt.color;
                                                console.log(artifact_alt);

                                                var art = gebi('art_amount');
                                                art.innerHTML = '<img src="' + (artifact_alt.picture != undefined ? artifact_alt.picture : artifact_alt.image) + '">';

                                                if (artifact_alt) $('#ask_confirm_type_amount').text(artifact_alt.kind);
                                                (artifact_alt.lev) ? $('#ask_confirm_level_amount').text(artifact_alt.lev.value) : $('#ask_confirm_level_amount').text(0);
                                            }

                                            $('#cart_amount_cnt').text(mx);


                                            $('#ask_confirm_title_amount').html(title).css('color', artifact_color);
                                            frame_content_hider.show();

                                            if (action) {
                                                $('#action_title_amount').html(action);
                                                if (action == 'Перемещение предмета') {
                                                    $('#ask_confirm_ok_container').html('<b class="butt1 pointer"><b><input value="Переместить" type="submit" onClick="ask_amount();" style="width: 100px;" class="redd" ></b></b>');
                                                } else if (action == 'Вернуть предмет в казну') {
                                                    $('#ask_amount_title').html('Вы уверены, что хотите вернуть предмет в казну?');
                                                    $('#ask_confirm_ok_container').html('<b class="butt1 pointer"><b><input value="Ok" type="submit" onClick="ask_amount();" style="width: 100px;" class="redd" ></b></b>');
                                                } else if (action == 'Переместить предмет') {
                                                    $('#ask_amount_title').html('Вы уверены, что хотите переместить предмет?');
                                                    $('#ask_confirm_ok_container').html('<b class="butt1 pointer"><b><input value="Ok" type="submit" onClick="ask_amount();" style="width: 100px;" class="redd" ></b></b>');
                                                } else {
                                                    $('#ask_confirm_ok_container').html('<b class="butt1 pointer"><b><input value="Добавить" type="submit" onClick="ask_amount();" style="width: 100px;" class="redd" ></b></b>');
                                                }
                                            } else {
                                                $('#action_title_amount').html('Видалення предмета');
                                                $('#ask_amount_title').html('Ви впевнені що хочете викинути?');
                                                $('#ask_confirm_ok_container').html('<b class="butt1 pointer"><b><input value="Викинути" type="submit" onClick="ask_amount();" style="width: 100px;" class="redd" ></b></b>');
                                            }
                                        }
                                    </script>
                                    <form id="cart_amount_form">
                                        <div id="cart_amount_div" style="display: none; position: fixed; z-index: 9999;">
                                            <div class="popup_global_container">
                                                <div class="popup-top-left">
                                                    <div class="popup-top-right">
                                                        <div class="popup-top-center">

                                                            <div class="popup_global_title"
                                                                 id="action_title_amount"></div>

                                                        </div>
                                                    </div>
                                                    <div class="popup_global_close_btn" onclick="closeDropPopup();"></div>
                                                </div>

                                                <div class="popup-left-center">
                                                    <div class="popup-right-center">

                                                        <div class="popup_global_content" style="padding: 20px;">
                                                            <div id="ask_amount_title" class="redd"
                                                                 style="text-align: center;"></div>
                                                            <span id="action_description_amount"></span>
                                                            <div class="popup-artifact">
                                                                <div class="popup-artifact__dsc">
                                                                    <span id="art_amount"
                                                                          class="popup-artifact__img"></span>
                                                                    <div id="ask_confirm_title_amount"
                                                                         class="popup-artifact__title popup-artifact__left"></div>
                                                                    <div><img src="images/tbl-shp_item-icon.gif"
                                                                              width="11" height="10"
                                                                              class="tbl-shp_item-ico" alt=""> <span
                                                                            id="ask_confirm_type_amount"></span></div>
                                                                    <div><img src="images/tbl-shp_level-icon.gif"
                                                                              width="11" height="10"
                                                                              class="tbl-shp_item-ico" alt=""> Уровень
                                                                        <span id="ask_confirm_level_amount"></span>
                                                                    </div>
                                                                    <div id="price_container_amount"
                                                                         style="display: none">Цена: <span
                                                                            id="price_amount"></span></div>
                                                                </div>
                                                            </div>

                                                            <div id="drop-qty-row" style="padding: 0 11px;">
                                                                <div class="cart-amount-sell-price"
                                                                     style="text-align: left;">
                                                                    <div>
                                                                        <b>Количество:</b>
                                                                        <span class="cart-amount-input-cont">
											<span class="b-input">
												<span class="b-input__inner">
													<span class="arrow left left-disabled"
                                                          onclick="counter_controller.left(this);"
                                                          title="Уменьшить кол-во"></span>
													<span class="arrow right" onclick="counter_controller.right(this);"
                                                          title="Увеличить кол-во"></span>
														<input id="cart_amount" type="text" value="1"
                                                               class="cart_amount_sell_input"
                                                               onchange="counter_controller.change(this, event);"
                                                               autocomplete="off"
                                                               onkeypress="counter_controller.keypress(this, event);">
												</span>
											</span>
										</span>
                                                                        из <span id="cart_amount_cnt"></span>
                                                                        <b class="butt1 pointer"><b><input value="Все"
                                                                                                           type="button"
                                                                                                           style="width: 100px;"
                                                                                                           class="redd"
                                                                                                           id="cart_amount_all"></b></b>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div style="clear: both; margin-top: 10px; text-align: center; padding: 0 11px;">
                                                                <span id="ask_confirm_ok_container"></span>
                                                                <b class="butt1 pointer"><b><input value="Отмена"
                                                                                                   type="button"
                                                                                                   onclick="closeDropPopup();"
                                                                                                   style="width: 100px;"
                                                                                                   class="redd"></b></b>
                                                            </div>
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
                                    </form>
                                    <script>

                                        var ask_amount_sell_title = null;

                                        var counter_controller = {
                                            _input: function (el) {
                                                return el.parentNode.querySelector('input');
                                            },
                                            _inner: function (el) {
                                                // el may be arrow span or input — climb to .b-input__inner
                                                var node = el.parentNode;
                                                while (node && !node.classList.contains('b-input__inner')) node = node.parentNode;
                                                return node;
                                            },
                                            left: function (el) {
                                                if (el.classList.contains('left-disabled')) return false;
                                                var input = counter_controller._input(el);
                                                var value = parseInt(input.value) - 1;
                                                if (value >= 1) input.value = value;
                                                counter_controller.change(input);
                                                return false;
                                            },
                                            right: function (el) {
                                                if (el.classList.contains('right-disabled')) return false;
                                                var input = counter_controller._input(el);
                                                var max   = parseInt(input.getAttribute('data-max-value')) || 1;
                                                var value = parseInt(input.value) + 1;
                                                if (value <= max) input.value = value;
                                                counter_controller.change(input);
                                                return false;
                                            },
                                            keypress: function (e) {
                                                var key = e.keyCode || e.which;
                                                if (key === 38) {
                                                    counter_controller.right(this.parentNode.querySelector('.arrow.right'));
                                                } else if (key === 40) {
                                                    counter_controller.left(this.parentNode.querySelector('.arrow.left'));
                                                } else {
                                                    counter_controller.change(this);
                                                }
                                                return false;
                                            },
                                            change: function (el) {
                                                var inner = counter_controller._inner(el);
                                                if (!inner) return false;
                                                var input = inner.querySelector('input');
                                                var min   = parseInt(input.getAttribute('data-min-value')) || 0;
                                                var max   = parseInt(input.getAttribute('data-max-value')) || 1;
                                                var value = parseInt(input.value) || 1;
                                                if (value >= max) { value = max; inner.querySelector('.arrow.right').classList.add('right-disabled'); }
                                                if (value <= min) { value = min; inner.querySelector('.arrow.left').classList.add('left-disabled'); }
                                                if (value > min)  { inner.querySelector('.arrow.left').classList.remove('left-disabled'); }
                                                if (value < max)  { inner.querySelector('.arrow.right').classList.remove('right-disabled'); }
                                                input.value = value;
                                                return false;
                                            }
                                        }
                                    </script>
                                    <form style="display: none; position: absolute; z-index: 9999;"
                                          id="cart_amount_sell_form">
                                        <div id="cart_amount_sell_div">
                                            <div class="popup_global_container">
                                                <div class="popup-top-left">
                                                    <div class="popup-top-right">
                                                        <div class="popup-top-center">

                                                            <div class="popup_global_title" id="action_title_sell">
                                                                Продаж предмета
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="popup_global_close_btn"
                                                         onclick="ask_amount_sell();"></div>
                                                </div>

                                                <div class="popup-left-center">
                                                    <div class="popup-right-center">

                                                        <div class="popup_global_content" style="padding: 20px;">
                                                            <div id="action_ask_confirm_sell" class="redd"
                                                                 style="text-align: center;"></div>
                                                            <span id="action_description_sell"></span>
                                                            <div class="popup-artifact">
                                                                <div class="popup-artifact__dsc">
                                                                    <span id="art_sell"
                                                                          class="popup-artifact__img"></span>
                                                                    <div id="ask_confirm_title_sell"
                                                                         class="popup-artifact__title popup-artifact__left"
                                                                         style="color: rgb(255, 0, 0);"></div>
                                                                    <div><img src="images/tbl-shp_item-icon.gif"
                                                                              width="11" height="10"
                                                                              class="tbl-shp_item-ico" alt=""> <span
                                                                            id="ask_confirm_type_sell"></span></div>
                                                                    <div><img src="images/tbl-shp_level-icon.gif"
                                                                              width="11" height="10"
                                                                              class="tbl-shp_item-ico" alt=""> Рівень
                                                                        <span id="ask_confirm_level_sell">0</span></div>
                                                                    <div id="price_container_sell"
                                                                         style="display: none">Ціна: <span
                                                                            id="price_sell"></span></div>
                                                                </div>
                                                            </div>

                                                            <div style="padding: 0 11px;">
                                                                <div id="cart_amount_sell_price"
                                                                     class="cart-amount-sell-price"
                                                                     style="text-align: left;">
                                                                    <div>
                                                                        <b>Кількість:</b>
                                                                        <span class="cart-amount-input-cont">
											<span class="b-input">
												<span class="b-input__inner">
													<span class="arrow left left-disabled"
                                                          onclick="counter_controller.left(this);"
                                                          title="Уменьшить кол-во"></span>
													<span class="arrow right" onclick="counter_controller.right(this);"
                                                          title="Увеличить кол-во"></span>
														<input id="cart_amount_sell" type="text" value="1"
                                                               class="cart_amount_sell_input"
                                                               onchange="counter_controller.change(this, event);"
                                                               autocomplete="off"
                                                               onkeypress="counter_controller.keypress(this, event);">
												</span>
											</span>
										</span>
                                                                        із <span id="cart_amount_sell_cnt">3</span>
                                                                        <b class="butt1 pointer"><b><input value="Все"
                                                                                                           type="button"
                                                                                                           style="width: 100px;"
                                                                                                           class="redd"
                                                                                                           id="cart_amount_sell_all"></b></b>
                                                                    </div>

                                                                    <div>
                                                                        <b>Ціна:</b> <span class="current_price"></span><span
                                                                            class="error" style="display: none;"></span>
                                                                    </div>
                                                                </div>

                                                                <div
                                                                    style="clear: both; margin-top: 10px; text-align: left;">
                                                                    <b class="butt1 pointer"><b><input value="Продати"
                                                                                                       type="submit"
                                                                                                       onclick="ask_amount_sell();"
                                                                                                       style="width: 100px;"
                                                                                                       class="redd"></b></b>
                                                                    <b class="butt1 pointer"><b><input value="Відміна"
                                                                                                       type="button"
                                                                                                       onclick="ask_amount_sell();"
                                                                                                       style="width: 100px;"
                                                                                                       class="redd"></b></b>
                                                                </div>
                                                            </div>
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
                                    </form>

                                    <script>

                                        var ask_confirm_title = null;
                                        var ask_confirm_title_actions = {
                                            "3": {
                                                "title": "ВИКИНУТИ",
                                                "title_popup": "Удаление предмета"
                                            },
                                            "5": {
                                                "title": "ПРОДАТИ",
                                                "price_block": "Цена продажи",
                                                "title_popup": "Продажа предмета"
                                            },
                                            "perp": {
                                                "title": "ОСТАВИТЬ у себя НАВСЕГДА",
                                                "price_block": "Стоимость"
                                            }
                                        };

                                        function ask_confirm(func, params) {
                                            ask_confirm_title = ask_confirm_title || $('#ask_confirm_title');

                                            var div = gebi('cart_confirm_div'), action_block = gebi('action_title');
                                            if (!func) {
                                                div.style.display = 'none';
                                                frame_content_hider.hide();
                                                return;
                                            }

                                            $(document).unbind('keyup');
                                            $(document).on('keyup', function (e) {
                                                if (e.keyCode == 13) { // Enter
                                                    $(this).unbind('keyup');
                                                    $('#cart_confirm_ok').trigger('click');
                                                } else if (e.keyCode == 27) { // Esc
                                                    $(this).unbind('keyup');
                                                    ask_confirm();
                                                }
                                            });

                                            var btn_name = '';

                                            if (params.action == 'drop') {
                                                $('#ask_confirm_ok_container_btn').html('<b class="butt1 pointer"><b><input value="Викинути" type="submit" onClick="if(document._submit)return false;document._submit=true;" class="redd" ID="cart_confirm_ok" style="width: 100px;"></b></b>');
                                            } else if (params.action == 'sell') {
                                                btn_name = 'Продати';
                                                $('#ask_confirm_ok_container_btn').html('<b class="butt1 pointer"><b><input value="Продати" type="submit" onClick="if(document._submit)return false;document._submit=true;" class="redd" ID="cart_confirm_ok" style="width: 100px;"></b></b>');
                                            } else {
                                                $('#ask_confirm_ok_container_btn').html('<b class="butt1 pointer"><b><input value="Ok" type="submit" onClick="if(document._submit)return false;document._submit=true;" class="redd" ID="cart_confirm_ok" style="width: 100px;"></b></b>');
                                            }

                                            var cart_confirm_ok = gebi('cart_confirm_ok');
                                            cart_confirm_ok.onclick = function () {
                                                this.onclick = function () {
                                                };
                                                func();
                                            };

                                            var action_price = gebi('action_price');
                                            action_price.style.display = 'none';

                                            var action_description = gebi('action_description');
                                            action_description.style.display = 'none';

                                            if (params) {
                                                var title = params.title || '';
                                                var action_title = params.action || {};
                                                var prices = params.prices || {};
                                                var artifact_id = params.artifact_id || null;
                                                var description = params.description || null;

                                                if (artifact_id) {
                                                    var artifact_alt = get_art_alt('AA_' + artifact_id);
                                                    var artifact_color = artifact_alt.color;
                                                }

                                                var action = action_title ? ask_confirm_title_actions[action_title] : {};

                                                if (params.action != 'rent' && params.action != 'harvest_cancel') {
                                                    var price = artifact_alt.price ? artifact_alt.price : 0;
                                                }


                                                if (prices && action != undefined && action.price_block) {
                                                    var money = prices.money ? prices.money : 0,
                                                        money_silver = prices.money_silver ? prices.money_silver : 0,
                                                        money_gold = prices.money_gold ? prices.money_gold : 0;

                                                    action_price.children[0].innerHTML = action.price_block;
                                                    action_price.children[1].innerHTML = html_money_str(1, money, money_silver, money_gold);
                                                    action_price.style.display = '';
                                                }

                                                if (description) {
                                                    action_description.innerHTML = description;
                                                    action_description.style.display = '';
                                                }

                                                var art = gebi('art');
                                                var action_ask_confirm = gebi('action_ask_confirm');

                                                if (params.action != 'rent' && params.action != 'harvest_cancel') {
                                                    if (artifact_alt.enchant_icon) {
                                                        art.innerHTML = '<img src="' + (artifact_alt.picture != undefined ? artifact_alt.picture : artifact_alt.image) + '">' + artifact_alt.enchant_icon;
                                                    } else {
                                                        art.innerHTML = '<img src="' + (artifact_alt.picture != undefined ? artifact_alt.picture : artifact_alt.image) + '">';
                                                    }
                                                } else {
                                                    gebi('popup_artifact').style.display = 'none';
                                                }

                                                var action_titl = '';
                                                try {
                                                    action_titl = action.title;
                                                } catch (e) {
                                                }
                                                action_ask_confirm.innerHTML = (params.question) ? params.question : ('Вы уверены, что хотите ' + action_titl + '?');
                                                action_block.innerHTML = ask_confirm_title_actions[params.action]['title_popup'] ? ask_confirm_title_actions[params.action]['title_popup'] : action_titl;
                                            }

                                            div.style.display = 'block';
                                            div.style.top = document.body.scrollTop + (document.body.clientHeight - div.offsetHeight) / 2;
                                            div.style.left = document.body.scrollLeft + (document.body.clientWidth - div.offsetWidth) / 2;

                                            if (title) ask_confirm_title.text(title);
                                            if (artifact_alt) $('#ask_confirm_type').text(artifact_alt.kind);
                                            if (artifact_alt && artifact_alt.lev) $('#ask_confirm_level').text(artifact_alt.lev.value);
                                            if (artifact_alt && artifact_alt.dur) {
                                                if (artifact_alt.flags2 && artifact_alt.flags2.crashproof) {
                                                    $('#ask_confirm_durability_amount').html('<span class="red">' + artifact_alt.flags2.crashproof + '</span>');
                                                } else {
                                                    $('#ask_confirm_durability_amount').html('<span class="red">' + artifact_alt.dur + '</span>/' + artifact_alt.dur_max);
                                                }
                                                $('#sellAskDurability').show();
                                            } else {
                                                $('#sellAskDurability').hide();
                                            }
                                            if (price) {
                                                //$('#price_container').show();
                                                $('#price').html(artifact_alt.price);
                                            }
                                            if (params.message) $('#ask_confirm_ms').html(params.message);
                                            if (artifact_color) ask_confirm_title.get(0).style.color = artifact_color;
                                            frame_content_hider.show();
                                            cart_confirm_ok.focus();
                                        }
                                    </script>
                                    <div style="display: none;" id="cart_confirm_div">
                                        <div class="popup_global_container">
                                            <div class="popup-top-left">
                                                <div class="popup-top-right">
                                                    <div class="popup-top-center">

                                                        <div class="popup_global_title" id="action_title"></div>

                                                    </div>
                                                </div>
                                                <div class="popup_global_close_btn" onclick="ask_confirm();"></div>
                                            </div>

                                            <div class="popup-left-center">
                                                <div class="popup-right-center">

                                                    <div class="popup_global_content" style="padding: 20px;">
                                                        <div id="action_ask_confirm" class="redd"
                                                             style="text-align: center;">Вы уверены, что хотите ПРОДАТЬ?
                                                        </div>
                                                        <span id="action_description"></span>
                                                        <div class="popup-artifact" id="popup_artifact">
                                                            <div class="popup-artifact__dsc">
                                                                <span id="art" class="popup-artifact__img"></span>
                                                                <div id="ask_confirm_title"
                                                                     class="popup-artifact__title popup-artifact__left"></div>
                                                                <div><img src="images/tbl-shp_item-icon.gif" width="11"
                                                                          height="10" class="tbl-shp_item-ico" alt="">
                                                                    <span id="ask_confirm_type"></span></div>
                                                                <div><img src="images/tbl-shp_level-icon.gif" width="11"
                                                                          height="10" class="tbl-shp_item-ico" alt="">
                                                                    Уровень <span id="ask_confirm_level"></span></div>
                                                                <div id="sellAskDurability" style=""><img
                                                                        src="images/tbl-shp_item-iznos.gif" width="11"
                                                                        height="10" class="tbl-shp_item-ico" alt="">
                                                                    Прочность <span
                                                                        id="ask_confirm_durability_amount"></span></div>
                                                                <div id="price_container" style="display: none">Цена:
                                                                    <span id="price"></span></div>
                                                            </div>
                                                        </div>
                                                        <div id="action_price" class="popup-artifact__action-price">
                                                            <b></b> <span></span></div>
                                                        <div style="clear: both; margin-top: 10px; text-align: center;">
                                                            <span id="ask_confirm_ok_container_btn"></span>
                                                            <b class="butt1 pointer"><b><input value="Отмена"
                                                                                               type="button"
                                                                                               onclick="ask_confirm();"
                                                                                               class="redd"
                                                                                               style="width: 100px;"></b></b>
                                                        </div>
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
                                    <style>
                                        @media screen and (max-width: 980px) {
                                            .sumka2_aizen {
                                                zoom: 1;
                                            }
                                        }
                                    </style>
{{--                                    <img src="{{ asset('img/bg/bg_inventory.png') }}" alt="" style="display: inline;position: absolute;height: 70%;right: 20px;bottom: 5px;opacity: .4;">--}}
                                    <table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0" class="sumka2_aizen">
{{--                                        <script src="/js/jquery-ui.js" type="text/javascript"></script>--}}
{{--                                        <script src="js/jstorages.js?ux=1550526374"></script>--}}

                                        <tbody>
                                        <tr>
                                            <td height="1">
                                                <table class="coll w100 p10h p2v brd2-all">
                                                    <tbody>
                                                    <tr class="bg_l">
                                                        <td nowrap=""><b>Вместимость:</b> &nbsp;<b class="redd"><span id="artifact_amount">{{ $data->getCountItems() }}</span>/<span id="artifact_amount_max">{{ $user->getBagCount() }}</span></b></td>
                                                        <td align="right" nowrap="">
                                                            <b>Монет:</b>
                                                            &nbsp;<b class="redd"><span title="Золотой"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->money) }} </b>
                                                            &nbsp;&nbsp;&nbsp<b class="redd"><span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ format_money($user->diamond) }} </b>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <br>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td valign="top">
                                                <div id="item_list" class="backpack_overflow lscroll">

                                                    @if($data->getGroup() === 'main')
                                                        @if($data->hasWeapon() || $data->hasShield())
                                                            <div id="bag_section" class="bag_section">
                                                                <div align="center">
                                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                                           style="margin: 0 auto;">
                                                                        <tbody>
                                                                        <tr height="22">
                                                                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                            <td align="center" class="tbl-usi-hdr mbg">Оружие и щиты</td>
                                                                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <br>
                                                                <ul class="lscroll backpack_list connected-sortable clearfix ui-sortable" style="">
                                                                    @if($data->hasWeapon())
                                                                        @foreach($data->getWeapon() as $item)
                                                                            <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">

                                                                                <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                    <tbody>
                                                                                    <tr>
                                                                                        <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                            &nbsp;
                                                                                            @if($item->count > 1)
                                                                                                <div class="bpdig">
                                                                                                    {{ $item->count }}
                                                                                                </div>
                                                                                            @endif
                                                                                            <span
                                                                                                style="position: absolute;right: -1px;top: 41px;"
                                                                                                act1="5" act2="5" act3="0" rune_h="0"
                                                                                                aid="23867698" art_id="" cnt="1"
                                                                                                div_id="AA_23867698" psell="16"
                                                                                                onmouseover="showItemInfo(this,event,2)"
                                                                                                onmouseout="showItemInfo(this,event,0)"
                                                                                                valign="bottom">
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </li>
                                                                        @endforeach
                                                                    @endif

                                                                    @if($data->hasShield())
                                                                        @foreach($data->getShield() as $item)
                                                                            <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">

                                                                                <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                                                       style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                    <tbody>
                                                                                    <tr>
                                                                                        <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                            &nbsp;
                                                                                            @if($item->count > 1)
                                                                                                <div class="bpdig">
                                                                                                    {{ $item->count }}
                                                                                                </div>
                                                                                            @endif
                                                                                            <span
                                                                                                style="position: absolute;right: -1px;top: 41px;"
                                                                                                onmouseover="showItemInfo(this,event,2)"
                                                                                                onmouseout="showItemInfo(this,event,0)"
                                                                                                valign="bottom">
                                                                                            </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </li>
                                                                        @endforeach
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                            <br>
                                                        @endif

                                                        @if($data->hasArmor())
                                                            <div id="bag_section_{{ random_int(1,100) }}" class="bag_section">
                                                                <div align="center">
                                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                                           style="margin: 0 auto;">
                                                                        <tbody>
                                                                        <tr height="22">
                                                                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                            <td align="center" class="tbl-usi-hdr mbg">Доспехи</td>
                                                                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <br>
                                                                <ul class="lscroll backpack_list connected-sortable clearfix ui-sortable" style="">
                                                                    @foreach($data->getArmor() as $item)
                                                                        <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">

                                                                            <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                                                   style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                        &nbsp;
                                                                                        @if($item->count > 1)
                                                                                            <div class="bpdig">
                                                                                                {{ $item->count }}
                                                                                            </div>
                                                                                        @endif
                                                                                        <span
                                                                                            style="position: absolute;right: -1px;top: 41px;"
                                                                                            act1="5" act2="5" act3="0" rune_h="0"
                                                                                            aid="23867698" art_id="" cnt="1"
                                                                                            div_id="AA_23867698" psell="16"
                                                                                            onmouseover="showItemInfo(this,event,2)"
                                                                                            onmouseout="showItemInfo(this,event,0)"
                                                                                            valign="bottom">
                                                                                            </span>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <br>
                                                        @endif

                                                        @if($data->hasBag() || $data->hasBelt())
                                                            <div id="bag_section" class="bag_section">
                                                                <div align="center">
                                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                                           style="margin: 0 auto;">
                                                                        <tbody>
                                                                        <tr height="22">
                                                                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                            <td align="center" class="tbl-usi-hdr mbg">Аксессуары</td>
                                                                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <br>
                                                                <ul class="lscroll backpack_list connected-sortable clearfix ui-sortable" style="">
                                                                    @if($data->hasBag())
                                                                        @foreach($data->getBag() as $item)
                                                                            <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">

                                                                                <table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                    <tbody>
                                                                                    <tr>
                                                                                        <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                            &nbsp;
                                                                                            @if($item->count > 1)
                                                                                                <div class="bpdig">
                                                                                                    {{ $item->count }}
                                                                                                </div>
                                                                                            @endif
                                                                                            <span
                                                                                                style="position: absolute;right: -1px;top: 41px;"
                                                                                                act1="5" act2="5" act3="0" rune_h="0"
                                                                                                aid="23867698" art_id="" cnt="1"
                                                                                                div_id="AA_23867698" psell="16"
                                                                                                onmouseover="showItemInfo(this,event,2)"
                                                                                                onmouseout="showItemInfo(this,event,0)"
                                                                                                valign="bottom">
                                                                                        </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </li>
                                                                        @endforeach
                                                                    @endif

                                                                    @if($data->hasBelt())
                                                                        @foreach($data->getBelt() as $item)
                                                                            <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">

                                                                                <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                                                       style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                    <tbody>
                                                                                    <tr>
                                                                                        <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                            &nbsp;
                                                                                            @if($item->count > 1)
                                                                                                <div class="bpdig">
                                                                                                    {{ $item->count }}
                                                                                                </div>
                                                                                            @endif
                                                                                            <span
                                                                                                style="position: absolute;right: -1px;top: 41px;"
                                                                                                act1="5" act2="5" act3="0" rune_h="0"
                                                                                                aid="23867698" art_id="" cnt="1"
                                                                                                div_id="AA_23867698" psell="16"
                                                                                                onmouseover="showItemInfo(this,event,2)"
                                                                                                onmouseout="showItemInfo(this,event,0)"
                                                                                                valign="bottom">
                                                                                        </span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    </tbody>
                                                                                </table>
                                                                            </li>
                                                                        @endforeach
                                                                    @endif
                                                                </ul>
                                                            </div>
                                                            <br>
                                                        @endif

                                                        @if($data->hasPotion())
                                                            <div id="bag_section_{{ random_int(1,100) }}" class="bag_section">
                                                                <div align="center">
                                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                                           style="margin: 0 auto;">
                                                                        <tbody>
                                                                        <tr height="22">
                                                                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                            <td align="center" class="tbl-usi-hdr mbg">Боевые эффекты</td>
                                                                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <br>
                                                                <ul class="lscroll backpack_list connected-sortable clearfix ui-sortable" style="">
                                                                    @foreach($data->getPotion() as $item)
                                                                        <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">

                                                                            <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                                                   style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                        &nbsp;
                                                                                        @if($item->count > 1)
                                                                                            <div class="bpdig">
                                                                                                {{ $item->count }}
                                                                                            </div>
                                                                                        @endif
                                                                                        <span
                                                                                            style="position: absolute;right: -1px;top: 41px;"
                                                                                            act1="5" act2="5" act3="0" rune_h="0"
                                                                                            aid="23867698" art_id="" cnt="1"
                                                                                            div_id="AA_23867698" psell="16"
                                                                                            onmouseover="showItemInfo(this,event,2)"
                                                                                            onmouseout="showItemInfo(this,event,0)"
                                                                                            valign="bottom">
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <br>
                                                        @endif

                                                        @if($data->hasResource())
                                                            <div id="bag_section_{{ random_int(1,100) }}" class="bag_section">
                                                                <div align="center">
                                                                    <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                                                                        <tbody>
                                                                        <tr height="22">
                                                                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                            <td align="center" class="tbl-usi-hdr mbg">Ресурсы</td>
                                                                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <br>
                                                                <ul class="lscroll backpack_list connected-sortable clearfix ui-sortable" style="">
                                                                    @foreach($data->getResource() as $item)
                                                                        <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">


                                                                            <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                                                   style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                        &nbsp;
                                                                                        @if($item->count > 1)
                                                                                            <div class="bpdig">
                                                                                                {{ $item->count }}
                                                                                            </div>
                                                                                        @endif
                                                                                        <span
                                                                                            style="position: absolute;right: -1px;top: 41px;"
                                                                                            onmouseover="showItemInfo(this,event,2)"
                                                                                            onmouseout="showItemInfo(this,event,0)"
                                                                                            valign="bottom">
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <br>
                                                        @endif


                                                        @if($data->hasScroll())
                                                            <div id="bag_section_{{ random_int(1,100) }}" class="bag_section">
                                                                <div align="center">
                                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                                           style="margin: 0 auto;">
                                                                        <tbody>
                                                                        <tr height="22">
                                                                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                            <td align="center" class="tbl-usi-hdr mbg">Свитки</td>
                                                                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <br>
                                                                <ul class="lscroll backpack_list connected-sortable clearfix ui-sortable" style="">
                                                                    @foreach($data->getScroll() as $item)
                                                                        <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">


                                                                            <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                                                   style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                        &nbsp;
                                                                                        @if($item->count > 1)
                                                                                            <div class="bpdig">
                                                                                                {{ $item->count }}
                                                                                            </div>
                                                                                        @endif
                                                                                        <span
                                                                                            style="position: absolute;right: -1px;top: 41px;"
                                                                                            act1="5" act2="5" act3="0" rune_h="0"
                                                                                            aid="23867698" art_id="" cnt="1"
                                                                                            div_id="AA_23867698" psell="16"
                                                                                            onmouseover="showItemInfo(this,event,2)"
                                                                                            onmouseout="showItemInfo(this,event,0)"
                                                                                            valign="bottom">
                                                                                    </span>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <br>
                                                        @endif

                                                        @if($data->hasRecipe())
                                                            <div id="bag_section_{{ random_int(1,100) }}" class="bag_section">
                                                                <div align="center">
                                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                                           style="margin: 0 auto;">
                                                                        <tbody>
                                                                        <tr height="22">
                                                                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                            <td align="center" class="tbl-usi-hdr mbg">Рецепты</td>
                                                                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <br>
                                                                <ul class="lscroll backpack_list connected-sortable clearfix ui-sortable" style="">
                                                                    @foreach($data->getRecipe() as $item)
                                                                        <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">


                                                                            <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                                                   style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                        &nbsp;
                                                                                        @if($item->count > 1)
                                                                                            <div class="bpdig">
                                                                                                {{ $item->count }}
                                                                                            </div>
                                                                                        @endif
                                                                                        <span
                                                                                            style="position: absolute;right: -1px;top: 41px;"
                                                                                            act1="5" act2="5" act3="0" rune_h="0"
                                                                                            aid="23867698" art_id="" cnt="1"
                                                                                            div_id="AA_23867698" psell="16"
                                                                                            onmouseover="showItemInfo(this,event,2)"
                                                                                            onmouseout="showItemInfo(this,event,0)"
                                                                                            valign="bottom">
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <br>
                                                        @endif

                                                        @if($data->hasGems())
                                                            <div id="bag_section_{{ random_int(1,100) }}" class="bag_section">
                                                                <div align="center">
                                                                    <table border="0" cellspacing="0" cellpadding="0"
                                                                           style="margin: 0 auto;">
                                                                        <tbody>
                                                                        <tr height="22">
                                                                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                                                            <td align="center" class="tbl-usi-hdr mbg">Камни</td>
                                                                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <br>
                                                                <ul class="lscroll backpack_list connected-sortable clearfix ui-sortable" style="">
                                                                    @foreach($data->getGems() as $item)
                                                                        <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">


                                                                            <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                                                   style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                        &nbsp;
                                                                                        @if($item->count > 1)
                                                                                            <div class="bpdig">
                                                                                                {{ $item->count }}
                                                                                            </div>
                                                                                        @endif
                                                                                        <span
                                                                                            style="position: absolute;right: -1px;top: 41px;"
                                                                                            onmouseover="showItemInfo(this,event,2)"
                                                                                            onmouseout="showItemInfo(this,event,0)"
                                                                                            valign="bottom">
                                                                                        </span>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                            <br>
                                                        @endif
                                                    @endif

                                                    @if($data->getGroup() === 'key' || $data->getGroup() === 'quest' || $data->getGroup() === 'artifact' || $data->getGroup() === 'gift')
                                                        <ul class="lscroll backpack_list connected-sortable clearfix ui-sortable" style="">
                                                            @foreach($data->getBackpack() as $item)
                                                                <li class="item ui-sortable-handle" data-backpack-id="{{ $item->id }}" style="opacity: 1;">

                                                                    <table width="50" height="50" cellpadding="0" cellspacing="0" border="0"
                                                                           style="float: left; margin: 1px; background: url('{{ asset($item->item->itemInfo->image) }}'); background-size: cover;">
                                                                        <tbody>
                                                                        <tr>
                                                                            <td data-id="{{ $item->item->id }}" data-type="{{ $item->item->itemInfo->type->value }}" data-equipped="{{ $item->isEquipped() ? '1' : '0' }}" data-count="{{ $item->count }}" data-name="{{ $item->item->itemInfo->name }}" data-image="{{ asset($item->item->itemInfo->image) }}" onclick="showCtxMenu(this, event)" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" valign="bottom">
                                                                                &nbsp;
                                                                                @if($item->count > 1)
                                                                                    <div class="bpdig">
                                                                                        {{ $item->count }}
                                                                                    </div>
                                                                                @endif
                                                                                <span
                                                                                    style="position: absolute;right: -1px;top: 41px;"
                                                                                    act1="5" act2="5" act3="0" rune_h="0"
                                                                                    aid="23867698" art_id="" cnt="1"
                                                                                    div_id="AA_23867698" psell="16"
                                                                                    onmouseover="showItemInfo(this,event,2)"
                                                                                    onmouseout="showItemInfo(this,event,0)"
                                                                                    valign="bottom">
                                                                                    </span>
                                                                            </td>
                                                                        </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    <script>
                                        var urlMODE = 'user_iframe.php?group=1&update_swf=1';
                                        var urlPUTON = 'action_run.php?code=PUT_ON&url_success=user_iframe.php%3Fgroup%3D1%26update_swf%3D1&url_error=user_iframe.php%3Fgroup%3D1%26update_swf%3D1'
                                        var urlPUTOFF = 'action_run.php?code=PUT_OFF&url_success=user_iframe.php%3Fgroup%3D1%26update_swf%3D1&url_error=user_iframe.php%3Fgroup%3D1%26update_swf%3D1'
                                        var urlDROP = 'action_run.php?code=DROP&url_success=user_iframe.php%3Fgroup%3D1%26ajax%3D1&url_error=user_iframe.php%3Fgroup%3D1'
                                        var urlSELL = 'action_run.php?code=SELL&url_success=user_iframe.php%3Fgroup%3D1%26ajax%3D1&url_error=user_iframe.php%3Fgroup%3D1'

                                        function artifactAct(obj, act, evnt) {

                                            if (typeof (iam_sorting_now) !== 'undefined' && iam_sorting_now)
                                                return false;
                                            var id = typeof (obj) == 'object' ? obj.getAttribute('aid') : obj;
                                            var artid = typeof (obj) == 'object' ? obj.getAttribute('artid') : obj;


                                            var rnd_url = '&_=' + (new Date().getTime() + Math.random());

                                            var artifact_title = '';
                                            var artifact_alt = get_art_alt('AA_' + id);

                                            if (artifact_alt) artifact_title = artifact_alt.title;

                                            var params = {
                                                title: artifact_title,
                                                artifact_id: id,
                                                action: act + "",
                                            };

                                            updateSwf({'lvl': '', 'items': ''});


                                            switch (act * 1) {
                                                case 1: // use
                                                    if (artid == 7698) {
                                                        _top().frames['main_frame'].frames['main'].location = "area_cube.php";
                                                        return;
                                                    }


                                                    showMsg('action_form.php?' + Math.random() + '&artifact_id=' + id + '&in[param_success][window_reload]=0&in[param_success][url_close]=user.php%3Fmode%3Dpersonage%26group%3D1%26update_swf%3D1');


                                                    break;
                                                case 2: // equip

                                                    slotNum = 0;
                                                    try {
                                                        slotNum = top.frames['main_frame']._slotNum;
                                                        isVariant = top.frames['main_frame']._isVariantSlot;
                                                        top.frames['main_frame']._slotNum = 0;
                                                        top.frames['main_frame']._isVariantSlot = 0;
                                                    } catch (e) {
                                                    }

                                                    if (obj.getAttribute('puton_confirm') > 0) {
                                                        top.systemConfirm('<span>После того как предмет будет надет, он станет непередаваемым.</span> <br><br>Вы уверены?', 'Действие', false, function () {

                                                            getUrl(urlPUTON + '&artifact_id=' + id + '&in[slot_num]=' + slotNum + '&in[variant_effect]=' + isVariant + '&ajax=1&out_ajax=1&update_swf=1', function (s, h, out) {
                                                                gebi('ajax_loader_inv').style.display = 'block';
                                                                try {
                                                                    getUrl('main_iframe.php?mode=update_swf&tar[]=inventory&tar[]=items&out_ajax=1', function (s1, h1, out1) {
                                                                        try {
                                                                            out1 = JSON.parse(out1);
                                                                            $.each(out1, function (index, out2) {
                                                                                switch (index) {
                                                                                    case 'vars':
                                                                                        $.each(out2, function (var_key, vars) {
                                                                                            swfObject(var_key, vars);
                                                                                        });
                                                                                        break;
                                                                                    case 'transfer_vars':
                                                                                        $.each(out2, function (var_key, vars) {
                                                                                            swfTransfer('small', var_key, 'from_small@' + vars);
                                                                                        });
                                                                                        break;
                                                                                }
                                                                                updateSwf({'lvl': '', 'items': ''});
                                                                            });
                                                                        } catch (e) {
                                                                            console.log(e);
                                                                        }
                                                                    });

                                                                    out = JSON.parse(out);
                                                                    if (out['status'] == 0) {
                                                                        getUrl('/user_iframe.php?group=1&only_artifacts=1', function (s, h, out) {
                                                                            gebi('item_list').innerHTML = out;
                                                                            gebi('ajax_loader_inv').style.display = 'none';
                                                                        });
                                                                    } else {
                                                                        gebi('ajax_loader_inv').style.display = 'none';
                                                                        showError((out['error'] !== undefined ? out['error'] : 'Ошибка'));
                                                                    }
                                                                } catch (e) {
                                                                    gebi('ajax_loader_inv').style.display = 'none';
                                                                }
                                                            });
                                                        });
                                                        break;
                                                    }

                                                    getUrl(urlPUTON + '&artifact_id=' + id + '&in[slot_num]=' + slotNum + '&in[variant_effect]=' + isVariant + '&ajax=1&out_ajax=1&update_swf=1', function (s, h, out) {
                                                        gebi('ajax_loader_inv').style.display = 'block';
                                                        try {
                                                            getUrl('main_iframe.php?mode=update_swf&tar[]=inventory&tar[]=items&out_ajax=1', function (s1, h1, out1) {
                                                                try {
                                                                    out1 = JSON.parse(out1);
                                                                    $.each(out1, function (index, out2) {
                                                                        switch (index) {
                                                                            case 'vars':
                                                                                $.each(out2, function (var_key, vars) {
                                                                                    swfObject(var_key, vars);
                                                                                });
                                                                                break;
                                                                            case 'transfer_vars':
                                                                                $.each(out2, function (var_key, vars) {
                                                                                    swfTransfer('small', var_key, 'from_small@' + vars);
                                                                                });
                                                                                break;
                                                                        }
                                                                        updateSwf({'lvl': '', 'items': ''});
                                                                    });
                                                                } catch (e) {
                                                                    console.log(e);
                                                                }
                                                            });

                                                            out = JSON.parse(out);
                                                            if (out['status'] == 0) {
                                                                getUrl('/user_iframe.php?group=1&only_artifacts=1', function (s, h, out) {
                                                                    gebi('item_list').innerHTML = out;
                                                                    gebi('ajax_loader_inv').style.display = 'none';
                                                                });
                                                            } else {
                                                                gebi('ajax_loader_inv').style.display = 'none';
                                                                showError((out['error'] !== undefined ? out['error'] : 'Ошибка'));
                                                            }
                                                        } catch (e) {
                                                            gebi('ajax_loader_inv').style.display = 'none';
                                                        }
                                                    });

                                                    break;
                                                case 3: // remove

                                                    params['action'] = "3";
                                                    if (obj.getAttribute('cnt') > 1) {
                                                        artifact_title = $('<span />').addClass('art_title').css({color: artifact_get_color(id)}).text(artifact_title).get(0).outerHTML;
                                                        artifact_title = '<br /> "' + artifact_title + '"';
                                                        params.title = artifact_title;
                                                        ask_amount(function (amount) {
                                                            ask_amount(null);
                                                            //location.href = urlDROP + '&artifact_id=' + id + '&in[amount]=' + amount + rnd_url;
                                                            getUrl(urlDROP + '&artifact_id=' + id + '&in[amount]=' + amount, function () {
                                                                getUrl('/user_iframe.php?group=1&only_artifacts=1', function (s, h, out) {
                                                                    gebi('item_list').innerHTML = out;
                                                                    gebi('ajax_loader_inv').style.display = 'none';
                                                                });
                                                            });
                                                            //getUrl(urlDROP +  '&artifact_id=' + id + '&in[amount]=' + amount + '&ajax=1' + rnd_url);
                                                        }, obj.getAttribute('cnt'), artifact_title, params.artifact_id);
                                                    } else {
                                                        ask_confirm(function () {
                                                            ask_confirm(null);
                                                            //location.href = urlDROP + '&artifact_id=' + id + '&in[amount]=1' + rnd_url;
                                                            getUrl(urlDROP + '&artifact_id=' + id + '&in[amount]=1', function () {
                                                                getUrl('/user_iframe.php?group=1&only_artifacts=1', function (s, h, out) {
                                                                    gebi('item_list').innerHTML = out;
                                                                    gebi('ajax_loader_inv').style.display = 'none';
                                                                });
                                                            });
                                                            //getUrl(urlDROP +  '&artifact_id=' + id + '&in[amount]=1' + '&ajax=1' + rnd_url);
                                                        }, params);
                                                    }
                                                    break;

                                                    if (obj.getAttribute('cnt') > 1) {
                                                        ask_amount(function (amount) {
                                                            gebi('cart_amount_div').style.display = 'none';
                                                            location.href = urlDROP + '&artifact_id=' + id + '&in[amount]=' + amount;
                                                            //getUrl(urlDROP + '&artifact_id=' + id + '&in[amount]=' + amount + '&ajax=1', function() {gebi('cart_amount_div').style.display = 'none';});
                                                        }, obj.getAttribute('cnt'))
                                                    } else {
                                                        ask_confirm(function () {
                                                            gebi('cart_amount_div').style.display = 'none';
                                                            location.href = urlDROP + '&artifact_id=' + id + '&in[amount]=1';
                                                            //getUrl(urlDROP + '&artifact_id=' + id + '&in[amount]=1' + '&ajax=1', function() {gebi('cart_confirm_div').style.display = 'none';});
                                                        })
                                                    }
                                                    break;
                                                case 4: // put off
                                                    //location.href = urlPUTOFF + '&artifact_id=' + id

                                                    getUrl(urlPUTOFF + '&artifact_id=' + id + '&ajax=1&out_ajax=1&update_swf=1', function (s, h, out) {
                                                        gebi('ajax_loader_inv').style.display = 'block';
                                                        try {
                                                            getUrl('main_iframe.php?mode=update_swf&tar[]=inventory&tar[]=items&out_ajax=1', function (s1, h1, out1) {
                                                                try {
                                                                    out1 = JSON.parse(out1);
                                                                    $.each(out1, function (index, out2) {
                                                                        switch (index) {
                                                                            case 'vars':
                                                                                $.each(out2, function (var_key, vars) {
                                                                                    swfObject(var_key, vars);
                                                                                });
                                                                                break;
                                                                            case 'transfer_vars':
                                                                                $.each(out2, function (var_key, vars) {
                                                                                    swfTransfer('small', var_key, 'from_small@' + vars);
                                                                                });
                                                                                break;
                                                                        }
                                                                    });
                                                                } catch (e) {
                                                                    console.log(e);
                                                                }
                                                            });

                                                            out = JSON.parse(out);
                                                            if (out['status'] == 0) {
                                                                getUrl('/user_iframe.php?group=1&only_artifacts=1', function (s, h, out) {
                                                                    gebi('item_list').innerHTML = out;
                                                                    gebi('ajax_loader_inv').style.display = 'none';
                                                                });
                                                            } else {
                                                                gebi('ajax_loader_inv').style.display = 'none';
                                                                showError((out['error'] !== undefined ? out['error'] : 'Ошибка'));
                                                            }
                                                        } catch (e) {
                                                            gebi('ajax_loader_inv').style.display = 'none';
                                                        }
                                                    });
                                                    //getUrl(urlPUTOFF + '&artifact_id=' + id + '&ajax=1');
                                                    break;
                                                case 5:
                                                    if (obj.getAttribute('gift') != null) break;
                                                    params.prices = artifact_calc_sell_price(obj);
                                                    params['action'] = "5";
                                                    var cnt = parseInt(obj.getAttribute('cnt'));
                                                    if (cnt == 0) {
                                                        ask_confirm(function () {
                                                            ask_confirm(null);
                                                            //getUrl(urlSELL +  '&artifact_id=' + id + '&in[amount]=1' + '&ajax=1' + rnd_url);
                                                            location.href = urlSELL + '&artifact_id=' + id + '&in[amount]=1' + rnd_url;
                                                        }, params);
                                                    } else {
                                                        // stack
                                                        artifact_title = $('<span />').addClass('art_title').css({color: artifact_get_color(id)}).text(artifact_title).get(0).outerHTML;
                                                        artifact_title = '"' + artifact_title + '"';
                                                        params.title = artifact_title;
                                                        ask_amount_sell(function (amount) {
                                                            ask_amount_sell(null);
                                                            location.href = urlSELL + '&artifact_id=' + id + '&in[amount]=' + amount + rnd_url;
                                                            //getUrl(urlSELL +  '&artifact_id=' + id + '&in[amount]=' + amount + '&ajax=1' + rnd_url);
                                                        }, cnt, params);
                                                    }
                                                    break;
                                                case 'sell': //sell
                                                    gebi('sell_cost').value = obj.getAttribute('psell');
                                                    gebi('sell_cnt').value = obj.getAttribute('cnt');
                                                    recalculate_sell_price();
                                                    if (obj.getAttribute('cnt') > 1) {
                                                        ask_amount_sell(function (amount) {
                                                            gebi('cart_amount_sell_div').style.display = 'none';
                                                            location.href = urlSELL + '&artifact_id=' + id + '&in[amount]=' + amount;
                                                        }, obj.getAttribute('cnt'))
                                                    } else {
                                                        ask_amount_sell(function () {
                                                            gebi('cart_amount_sell_div').style.display = 'none';
                                                            location.href = urlSELL + '&artifact_id=' + id + '&in[amount]=1';
                                                        })
                                                    }
                                                    break;
                                            }
                                        }
                                    </script>
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
    {{-- Страница вложена в обёртку раздела «Вещи»: top — игровое окно, parent — обёртка --}}
    @if (session()->has('message'))
        try { window.top.showErrorIframe('{{ session('message') }}') } catch(e) {}
    @endif
    @if (session()->has('hotbar_refresh'))
        try { window.top.refreshHotbar(); } catch(e) {}
    @endif
    @if (session()->has('equip_changed'))
        try { window.parent.reloadEquip(); } catch(e) {}
    @endif

    document.addEventListener('keydown', function(event) {
        switch (event.key.toLowerCase()) {
            case ' ':
                window.top.sendDataToGame('{{ route('location') }}');
                break;
            default:
                return;
        }
        event.preventDefault();
    });
</script>

<script>
    window.addEventListener('message', function(e) {
        if (!e.data || !e.data.backpack_update) return;

        const { item_id, count, removed } = e.data.backpack_update;

        // Ищем td с data-id совпадающим с item_id
        const td = document.querySelector('td[data-id="' + item_id + '"]');
        if (!td) return;

        if (removed) {
            // Удаляем родительский <li>
            const li = td.closest('li.item');
            if (li) li.remove();
        } else {
            // Обновляем или скрываем .bpdig
            let bpdig = td.querySelector('.bpdig');
            if (count > 1) {
                if (!bpdig) {
                    bpdig = document.createElement('div');
                    bpdig.className = 'bpdig';
                    td.appendChild(bpdig);
                }
                bpdig.textContent = count;
            } else if (bpdig) {
                bpdig.remove();
            }
        }
    });
</script>

<!-- Context Menu -->
<div id="drop-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:9990;"></div>

<div id="item-ctx-menu" style="display:none; position:fixed; z-index:9999; background:#f5e9d0; border:2px solid #CEBBAA; border-radius:4px; box-shadow:2px 2px 6px rgba(0,0,0,0.35); min-width:130px; font-family:Tahoma; font-size:11px; overflow:hidden;">
    <div class="ctx-item" id="ctx-info"    onclick="ctxAction('info')">Информация</div>
    <div class="ctx-item" id="ctx-equip"   onclick="ctxAction('equip')">Надеть</div>
    <div class="ctx-item" id="ctx-unequip" onclick="ctxAction('unequip')">Снять</div>
    <div class="ctx-item" id="ctx-use"     onclick="ctxAction('use')">Использовать</div>
    <div class="ctx-item" id="ctx-chat" onclick="ctxAction('chat')">Отправить в чат</div>
    <div class="ctx-item ctx-danger" id="ctx-drop" onclick="ctxAction('drop')">Выкинуть</div>
</div>
<style>
    .ctx-item { padding: 6px 14px; cursor: pointer; color: #461c0b; border-bottom: 1px solid #e0ccb0; }
    .ctx-item:last-child { border-bottom: none; }
    .ctx-item:hover { background: #e8c06a; }
    .ctx-danger { color: #a00; }
    .ctx-danger:hover { background: #f5c0b0; }
</style>
<script>
    var frame_content_hider = {
        show: function() { gebi('drop-overlay').style.display = 'block'; },
        hide: function() { gebi('drop-overlay').style.display = 'none'; }
    };

    function closeDropPopup() {
        gebi('cart_amount_div').style.display = 'none';
        frame_content_hider.hide();
    }

    var _ctxItemId    = null;
    var _ctxItemType  = null;
    var _ctxItemCount = 1;
    var _ctxItemName  = '';
    var _ctxItemImage = '';

    function showCtxMenu(el, event) {
        event.stopPropagation();
        // скрываем тултип и блокируем mousemove пока меню открыто
        try { showItemInfo(el, event, 0); } catch(e) {}
        document.onmousemove = function(){};
        _ctxItemId    = el.getAttribute('data-id');
        _ctxItemType  = el.getAttribute('data-type');
        _ctxItemCount = parseInt(el.getAttribute('data-count')) || 1;
        _ctxItemName  = el.getAttribute('data-name') || '';
        _ctxItemImage = el.getAttribute('data-image') || '';
        var type     = _ctxItemType;
        var equipped = el.getAttribute('data-equipped') === '1';

        var equippable = ['weapon','shield','armor','belt','bag'].indexOf(type) !== -1;
        var usable     = ['potion','eat','scroll','artifact','chest','gift','key'].indexOf(type) !== -1;

        document.getElementById('ctx-equip').style.display   = (equippable && !equipped) ? '' : 'none';
        document.getElementById('ctx-unequip').style.display = equipped ? '' : 'none';
        document.getElementById('ctx-use').style.display     = usable   ? '' : 'none';

        var menu = document.getElementById('item-ctx-menu');
        menu.style.display = 'block';

        var x = event.clientX, y = event.clientY;
        menu.style.left = x + 'px';
        menu.style.top  = y + 'px';

        // keep inside viewport
        var rect = menu.getBoundingClientRect();
        if (rect.right  > window.innerWidth)  menu.style.left = (x - rect.width)  + 'px';
        if (rect.bottom > window.innerHeight) menu.style.top  = (y - rect.height) + 'px';
    }

    function ctxAction(action) {
        var id = _ctxItemId;
        document.getElementById('item-ctx-menu').style.display = 'none';
        var base = '{{ url("/items") }}';
        switch (action) {
            case 'info':    showArtifactInfo(id, null, null, null); break;
            case 'equip':   location.href = base + '/put-on/'     + id; break;
            case 'unequip': location.href = base + '/put-off/'    + id; break;
            case 'use':
                if (_ctxItemType === 'chest') {
                    location.href = base + '/open-chest/' + id;
                } else {
                    useItemAjax(id);
                }
                break;
            case 'drop':
                showDropDialog(id, _ctxItemCount);
                break;
            case 'chat':
                try {
                    var chatFrame   = window.top.document.getElementById('chat-frame');
                    var bottomFrame = chatFrame.contentDocument.getElementById('bottom-frame');
                    bottomFrame.contentWindow.postMessage({ type: 'insertItem', code: '[[item_' + id + ']] ' }, '*');
                } catch(e) {}
                break;
        }
    }

    function useItemAjax(itemId) {
        fetch('{{ route('items.use', ['id' => '__ID__']) }}'.replace('__ID__', itemId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status !== 'success') {
                try { window.top.showErrorIframe(data.message || 'Ошибка'); } catch(e) { alert(data.message || 'Ошибка'); }
                return;
            }

            // Обновляем счётчик или удаляем предмет из рюкзака
            var td = document.querySelector('td[data-id="' + itemId + '"]');
            if (td) {
                if (data.removed) {
                    var li = td.closest('li.item');
                    if (li) li.remove();
                } else {
                    var bpdig = td.querySelector('.bpdig');
                    if (data.count > 1) {
                        if (!bpdig) {
                            bpdig = document.createElement('div');
                            bpdig.className = 'bpdig';
                            td.appendChild(bpdig);
                        }
                        bpdig.textContent = data.count;
                    } else if (bpdig) {
                        bpdig.remove();
                    }
                    td.setAttribute('data-count', data.count);
                }
            }

            // Отправляем HP/MP в character-frame
            var hpMp = {
                hp: { current: data.hp_now, max: data.hp_max },
                mp: { current: data.mp_now, max: data.mp_max },
            };
            try { window.top.sendToFrame('character-frame', hpMp); } catch(e) {}
            try { window.top.refreshHotbar(); } catch(e) {}
        })
        .catch(function() {
            try { window.top.showErrorIframe('Ошибка использования предмета'); } catch(e) {}
        });
    }

    function showDropDialog(itemId, count) {
        var base = '{{ url("/items") }}';
        gebi('action_title_amount').innerHTML = 'Выброс предмета';
        gebi('ask_amount_title').innerHTML    = 'Вы уверены, что хотите выбросить предмет?';
        gebi('art_amount').innerHTML          = _ctxItemImage ? '<img src="' + _ctxItemImage + '" style="width:50px;height:50px;">' : '';
        gebi('ask_confirm_title_amount').innerHTML = _ctxItemName;

        var qtyRow = gebi('drop-qty-row');
        if (count > 1) {
            qtyRow.style.display = '';
            var input = gebi('cart_amount');
            input.value = 1;
            input.setAttribute('data-min-value', 1);
            input.setAttribute('data-max-value', count);
            input.parentNode.querySelector('.arrow.left').classList.add('left-disabled');
            input.parentNode.querySelector('.arrow.right').classList.remove('right-disabled');
            gebi('cart_amount_cnt').textContent = count;
            gebi('cart_amount_all').onclick = function () {
                gebi('cart_amount').value = count;
            };
            gebi('ask_confirm_ok_container').innerHTML =
                '<b class="butt1 pointer"><b><input value="Выбросить" type="button" onclick="doDropItem(' + itemId + ', \'' + base + '\')" style="width: 100px;" class="redd"></b></b>';
        } else {
            qtyRow.style.display = 'none';
            gebi('ask_confirm_ok_container').innerHTML =
                '<b class="butt1 pointer"><b><input value="Выбросить" type="button" onclick="location.href=\'' + base + '/drop/' + itemId + '?c=1\'" style="width: 100px;" class="redd"></b></b>';
        }

        var div = gebi('cart_amount_div');
        div.style.display = 'block';
        div.style.top  = Math.max(0, (window.innerHeight - div.offsetHeight) / 2) + 'px';
        div.style.left = Math.max(0, (window.innerWidth  - div.offsetWidth)  / 2) + 'px';
        frame_content_hider.show();
    }

    function doDropItem(itemId, base) {
        var qty = parseInt(gebi('cart_amount').value) || 1;
        gebi('cart_amount_div').style.display = 'none';
        try { frame_content_hider.hide(); } catch(e) {}
        location.href = base + '/drop/' + itemId + '?c=1&qty=' + qty;
    }

    document.addEventListener('click', function () {
        document.getElementById('item-ctx-menu').style.display = 'none';
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.backpack_list').forEach(function (list) {
            Sortable.create(list, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function () {
                    var ids = Array.from(list.querySelectorAll('[data-backpack-id]'))
                        .map(function (el) { return el.getAttribute('data-backpack-id'); });

                    fetch('{{ route('backpack.order') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ ids: ids })
                    });
                }
            });
        });
    });
</script>

</body>
</html>
