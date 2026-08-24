<!DOCTYPE html>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('main/style/bank-bundles.css') }}">
    <style>
        body, td, div, span, p, li, ol, ul, input, a, b, label { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; }
        h3 { font-size: 14px; font-family: Tahoma, Geneva, sans-serif; }
        h4 { font-size: 12px; font-family: Tahoma, Geneva, sans-serif; }
        a { text-decoration: none; }

        /* Base layout */
        .bgg { background-image: url({{ asset('img/bg/bgg.gif') }}); }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .brd2-all { border: 1px solid #db9f73; }
        .brd2-top { border-top: 1px solid #db9f73; }
        .brd2, .brd2 td { border: 1px solid #db9f73; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .w100 { width: 100%; }
        .h100 { height: 100%; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .p4v { padding-top: 4px; padding-bottom: 4px; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .regblk, .regblk * { color: #49382d; }
        .redd, .redd * { color: #BA0000 !important; }
        .grnn, .grnn * { color: #114d01 !important; }
        .pointer, .pointer input { cursor: pointer; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .clearfix:after { content: ''; display: table; clear: both; }
        .premium-features { width: 100%; }
        .premium-features-header { font-size: 12px; font-weight: bold; }

        /* tbl-shp panel borders */
        .tbl-shp-sml { background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sides { background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-usi_bg  { background-image: url('/img/bg/tbl-usi_bg.gif'); background-repeat: repeat; }
        .tbl-usi-hdr { background: url('/main/images/tbl-usi-hdr.gif') no-repeat; font-family: tahoma, sans-serif; height: 22px; }
        .tbl-usi-hdr.lc { background-position: left -25px; width: 27px; }
        .tbl-usi-hdr.rc { background-position: right 0; width: 27px; }
        .tbl-usi-hdr.mbg { background-position: center -50px; background-repeat: repeat-x; color: #FCF5B7; font-size: 11px; font-weight: bold; height: 16px; padding: 1px 10px 5px; line-height: 16px; vertical-align: middle; }
        .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b { display: block; height: 22px; font-size: 0; overflow: hidden; width: 27px; }

        /* Payment block outer frame */
        .common-inner-block-tl { background: url('/main/images/common-inner-block-tl.png') no-repeat; }
        .common-inner-block-tr { background: url('/main/images/common-inner-block-tr.png') no-repeat; }
        .common-inner-block-bl { background: url('/main/images/common-inner-block-bl.png') no-repeat; }
        .common-inner-block-br { background: url('/main/images/common-inner-block-br.png') no-repeat; }
        .common-inner-block-t  { background: url('/main/images/common-inner-block-t.png') repeat-x; }
        .common-inner-block-b  { background: url('/main/images/common-inner-block-b.png') repeat-x; }
        .common-inner-block-l  { background: url('/main/images/common-inner-block-l.png') repeat-y; }
        .common-inner-block-r  { background: url('/main/images/common-inner-block-r.png') repeat-y; }
        .common-inner-block-bg { background: url('/img/bg/bg_l.gif'); }
        .common-inner-block__mh { padding: 3px; }

        /* Payment block inner frame */
        .common-inset-tl { background: url('/main/images/common-inset-tl.png') no-repeat; }
        .common-inset-tr { background: url('/main/images/common-inset-tr.png') no-repeat; }
        .common-inset-bl { background: url('/main/images/common-inset-bl.png') no-repeat; }
        .common-inset-br { background: url('/main/images/common-inset-br.png') no-repeat; }
        .common-inset-t  { background: url('/main/images/common-inset-t.png') repeat-x; }
        .common-inset-b  { background: url('/main/images/common-inset-b.png') repeat-x; }
        .common-inset-l  { background: url('/main/images/common-inset-l.png') repeat-y; }
        .common-inset-r  { background: url('/main/images/common-inset-r.png') repeat-y; }
        .common-inset-bg { background: #ffffd6; }

        /* Vertical payment method tabs */
        .set-tabs { float: left; width: 92px; margin-right: -6px; }
        .set-tabs .tab { position: relative; z-index: 1; width: 88px; height: 58px; padding: 10px 2px; text-align: center; cursor: pointer; background: url('/main/images/set-tabs.png') 0 100% no-repeat; }
        .set-tabs .tab-active { z-index: 3; cursor: default; background-position: 0 0; }
        .set-tabs-content { position: relative; z-index: 2; overflow: hidden; }

        /* Buy button + tooltip (prod inline styles) */
        #submit-button { display: block; width: 100%; padding: 10px; margin-top: -9px; font-size: 16px; }
        #submit-button:disabled { opacity: 0.7; cursor: not-allowed; }
        input[type="checkbox"] { margin-right: 10px; }
        .tooltip-container { position: relative; display: inline-block; }
        .tooltip-text { visibility: visible; width: 200px; background-color: black; color: #fff; text-align: center; border-radius: 5px; padding: 5px; position: absolute; z-index: 1; bottom: 30%; left: 50%; margin-left: -100px; opacity: 0; transition: opacity 0.3s; }
        .tooltip-container:hover .tooltip-text { opacity: 1; position: relative; top: 0; left: 68px; }
        #submit-button:disabled:hover + .tooltip-text { opacity: 1; }

        /* Step wizards (prod inline styles) */
        #dwar-usdt-box, #dwar-volet-box {
            width: 530px; max-height: 500px; background: #fff2d1; border: 1px solid #a06a2c;
            box-shadow: inset 0 0 10px #d4b27c; font-family: Tahoma, Arial, sans-serif; color: #3b220f; text-align: left;
        }
        .dwar-steps { display: flex; background: linear-gradient(#e2b86b, #c79242); border-bottom: 1px solid #a06a2c; }
        .dwar-steps span { flex: 1; padding: 6px; font-weight: bold; cursor: pointer; text-align: center; }
        .dwar-steps span.active { background: #8c4f19; color: #fff2c0; }
        .dwar-content { padding: 10px; max-height: 450px; overflow-y: auto; }
        .step { display: none; }
        .step.active { display: block; }
        .wallet-box { background: #edd6a4; border: 1px solid #b0803c; padding: 8px; }
        .wallet { background: #fff4d8; border: 1px solid #b0803c; padding: 6px; font-family: monospace; }
        .wallet span { float: right; cursor: pointer; }
        .warn { color: #8b0000; font-weight: bold; margin-top: 5px; }
        .nav { margin-top: 10px; }

        /* Diamond calculator (prod inline styles) */
        #dwar-calc-box, #dwar-calc-box2 {
            width: 510px; background: #fff2d1; border: 1px solid #a06a2c; box-shadow: inset 0 0 10px #d4b27c;
            font-family: Tahoma, Arial, sans-serif; color: #3b220f; text-align: left; padding: 10px;
        }
        .calc-title { font-size: 14px; font-weight: bold; margin-bottom: 8px; }
        .calc-box-inner { background: #edd6a4; border: 1px solid #b0803c; padding: 10px; }
        .calc-input { background: #fff4d8; border: 1px solid #b0803c; padding: 4px; font-family: monospace; width: 120px; }
        .calc-result { margin-top: 8px; font-weight: bold; font-size: 13px; color: #8c4f19; }

        @media (max-width: 600px) {
            #dwar-usdt-box, #dwar-volet-box, #dwar-calc-box, #dwar-calc-box2 { width: 100%; max-height: 100vh; }
        }
    </style>
</head>
<body leftmargin="0" rightmargin="0" class="regblk">

@include('premium_shop::_nav', ['activeMenu' => 'topup'])

<table class="coll w100 h100">
<tr><td valign="top" height="100%">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr height="100%">
    <td class="bgg" align="center" valign="top" width="100%">

        <table class="coll w100" height="100%">
        <tr>

            {{-- Payment area --}}
            <td valign="top">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                        <tr height="22">
                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                            <td align="center" class="tbl-usi-hdr mbg">Пополнение счёта</td>
                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                        </tr>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" align="left" style="padding:6px 4px;">

                        {{-- Balance row --}}
                        <table class="coll w100 p10h p2v brd2-all">
                        <tr class="bg_l">
                            <td align="left" nowrap width="20%"><b>У вас:</b>&nbsp;&nbsp;&nbsp;
                                <b class="redd"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle"> {{ format_money($user->money) }}</b>
                                &nbsp;&nbsp;&nbsp;<b class="redd"><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" align="absmiddle"> {{ format_money($user->diamond) }}</b>
                            </td>
                            <td>
                                <b class="butt2 pointer"><b><input value="Обмен" type="button" onclick="if(document._submit)return false;document._submit=true;location.href='{{ route('bank') }}';" style="width:60px"></b></b>
                            </td>
                        </tr>
                        </table>
                        <br>

                        <center>
                        <table width="100%" border="0" cellspacing="0" cellpadding="0"><tbody><tr>
                        <td valign="top" align="left" style="padding:6px 4px; width:650px;">

                            {{-- Tabs --}}
                            <div class="set-tabs bank-premium">
                                <div class="tab tab-active">
                                    <img src="{{ asset('main/images/pay/visa1.png') }}" width="85%">
                                </div>
                            </div>
                            {{-- //Tabs --}}

                            <div class="set-tabs-content" style="width:600px;">

                            {{-- === BLOCK 1: UAH === --}}
                            <table border="0" cellpadding="0" cellspacing="0" class="w100 jqSet">
                            <tr>
                                <td width="10" height="10" class="common-inner-block-tl"></td>
                                <td height="10" class="common-inner-block-t"></td>
                                <td width="10" height="10" class="common-inner-block-tr"></td>
                            </tr>
                            <tr>
                                <td class="common-inner-block-l"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                                <td class="common-inner-block-bg">
                                <div class="common-inner-block__mh">
                                    <table class="w100" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="12" height="12" class="common-inset-tl"></td>
                                        <td height="12" class="common-inset-t"></td>
                                        <td width="12" height="12" class="common-inset-tr"></td>
                                    </tr>
                                    <tr>
                                        <td width="12" class="common-inset-l"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                        <td height="12" class="common-inset-bg">
                                            <center>
                                            <table class="premium-features">
                                            <tr><td width="50%">
                                            <div class="clearfix">
                                            <div class="premium-features-text" style="text-align:center">
                                                <span style="font-size:12px;"><b style="color:darkred;">Курс: 1 алмаз = 0.15 доллара или 6.50 грн (на сегодня)</b><br>

                                                <p><b style="color:#B22222;font-size:15px;">Ваш ID: </b><b style="color:#FF0000;font-size:15px;">{{ $user->id }}</b></p>

                                                <div class="bank-bundles" style="position: relative; top: 0px; left: 0px;">
                                                <div class="bank-bundles__item pack__custom">
                                                    <div class="bank-bundles__item-head">
                                                        <div class="bank-bundles__item-title">
                                                            <span>Курс алмазов</span>
                                                        </div>
                                                    </div>
                                                    <div class="bank-bundles__item-body" style="color:#f7edaa;">
                                                        <div class="bank-bundles__item-picture-wrapper">
                                                            <div class="bank-bundles__item-picture">
                                                                <img src="{{ asset('main/images/data/bank_pack_picture/diamonds-3_200.png') }}" alt="">
                                                            </div>
                                                        </div>
                                                        <div class="bank-bundles__item-controls">
                                                            <div class="bank-bundles__item-controls-type">
                                                                <img src="{{ asset('main/images/bank/bundle/money-gold.png') }}" alt="" style="width: 22px">
                                                                <i class="bank-bundles__item-controls-arrow arrow-left" onmousedown="calc_exchange('bank-bundle-x0-1', 'bank-bundle-x1-1', 0, -1, 6.50)"></i><input type="text" id="bank-bundle-x0-1" size="5" maxlength="6" value="1" onkeyup="calc_exchange('bank-bundle-x0-1', 'bank-bundle-x1-1', 0, 0, 6.50)" onfocus="this.select()"><i class="bank-bundles__item-controls-arrow arrow-right" onmousedown="calc_exchange('bank-bundle-x0-1', 'bank-bundle-x1-1', 0, 1, 6.50)"></i>
                                                            </div>
                                                            <span class="bank-bundles__item-exchange"><img src="{{ asset('img/icon/arrow.gif') }}" alt=""></span>
                                                            <div class="bank-bundles__item-controls-type">
                                                                <span class="bank-bundles__item-controls-label" data-title="₴">₴</span>
                                                                <i class="bank-bundles__item-controls-arrow arrow-left" onmousedown="calc_exchange('bank-bundle-x1-1', 'bank-bundle-x0-1', 1, -1, 6.50)"></i><input name="f-amount" type="text" id="bank-bundle-x1-1" size="5" maxlength="6" value="6.50" onkeyup="calc_exchange('bank-bundle-x1-1', 'bank-bundle-x0-1', 1, 0, 6.50)" onfocus="this.select()"><i class="bank-bundles__item-controls-arrow arrow-right" onmousedown="calc_exchange('bank-bundle-x1-1', 'bank-bundle-x0-1', 1, 1, 6.50)"></i>
                                                            </div>
                                                        </div>
                                                        <div class="bank-bundles__item-bar">
                                                            <form id="top-up-form">
                                                                <label style="position:relative;top:-7px;">
                                                                    <input type="checkbox" id="agree-checkbox" style="position:relative;top:2px;"> <b style="color:#fffc00;">Я соглашаюсь с правилами</b>
                                                                </label>
                                                                <div class="tooltip-container">
                                                                    <button id="submit-button" type="submit" class="bank-bundles__item-button" disabled>
                                                                        <span data-title="Купить" style="position:relative;top:-9px;">Купить</span>
                                                                    </button>
                                                                    <span class="tooltip-text" id="tooltip-text">Сначала согласитесь с правилами</span>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                </div>

                                                <br>
                                                - Средства возврату не подлежат<br>
                                                - Начисление от 5 мин до 6 часов<br>в период с 09:00 по 23:00<br>
                                                - Автоматическая конвертация из любой валюты в ГРН<br>
                                                - <b style="color:#fff200;">Минимальная сумма пополнения 500 грн</b> — если сумма будет меньше, вам ничего не начислится
                                                <br><br><br>
                                                <b style="color:red;">При желании вы можете пополнить счёт другому игроку, указав его ID</b><br><b style="color:green;">Начисление на баланс: от 5 мин до 6 часов<br>Рабочее время: с 09:00 по 23:00</b>, в другое время лучше не пополнять, если не хотите ждать до утра<br>
                                                Администратор: <b>Aizen&nbsp;[12]</b>
                                                </span>
                                            </div>
                                            </div>
                                            </td></tr>
                                            </table>
                                            </center>
                                        </td>
                                        <td width="12" class="common-inset-r"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                    </tr>
                                    <tr>
                                        <td width="12" height="12" class="common-inset-bl"></td>
                                        <td height="12" class="common-inset-b"></td>
                                        <td width="12" height="12" class="common-inset-br"></td>
                                    </tr>
                                    </table>
                                </div>
                                </td>
                                <td class="common-inner-block-r"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                            </tr>
                            <tr>
                                <td width="10" height="10" class="common-inner-block-bl"></td>
                                <td height="10" class="common-inner-block-b"></td>
                                <td width="10" height="10" class="common-inner-block-br"></td>
                            </tr>
                            </table>
                            {{-- === /BLOCK 1 === --}}

                            {{-- === BLOCK 2: USDT TRC20 === --}}
                            <table border="0" cellpadding="0" cellspacing="0" class="w100 jqSet">
                            <tr>
                                <td width="10" height="10" class="common-inner-block-tl"></td>
                                <td height="10" class="common-inner-block-t"></td>
                                <td width="10" height="10" class="common-inner-block-tr"></td>
                            </tr>
                            <tr>
                                <td class="common-inner-block-l"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                                <td class="common-inner-block-bg">
                                <div class="common-inner-block__mh">
                                    <table class="w100" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="12" height="12" class="common-inset-tl"></td>
                                        <td height="12" class="common-inset-t"></td>
                                        <td width="12" height="12" class="common-inset-tr"></td>
                                    </tr>
                                    <tr>
                                        <td width="12" class="common-inset-l"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                        <td class="common-inset-bg">
                                            <center>
                                            <table class="premium-features">
                                            <tr><td width="50%">
                                            <div class="clearfix">
                                            <div class="premium-features-text" style="text-align:center">
                                            <div id="dwar-usdt-box">
                                                <div class="dwar-steps">
                                                    <span class="active" onclick="showStep(0)">Общее</span>
                                                    <span onclick="showStep(1)">Шаг 1</span>
                                                    <span onclick="showStep(2)">Шаг 2</span>
                                                    <span onclick="showStep(3)">Шаг 3</span>
                                                    <span onclick="showStep(4)">Готово</span>
                                                </div>
                                                <div class="dwar-content">
                                                    <div class="step active">
                                                        <h3>Оплата через USDT (TRC20)</h3>
                                                        <p>Пополнение счёта через <b>USDT (TRON / TRC20)</b> — быстрый и безопасный способ оплаты.</p>
                                                        <ul>
                                                            <li>✔ Работает по всему миру</li>
                                                            <li>✔ Минимальная сумма пополнения 15 долларов (USDT)</li>
                                                            <li>✔ Минимальная комиссия 1 доллар на любую сумму</li>
                                                            <li>✔ Курс: 1 алмаз = 0.15 доллара (на сегодня)</li>
                                                            <li>❗Другие сети не поддерживаются</li>
                                                        </ul>
                                                        <div class="nav"><b class="butt1 pointer"><b><input value="Далее →" type="submit" class="grnn" style="font-size:9px!important" onclick="showStep(1);return false;"></b></b></div>
                                                    </div>
                                                    <div class="step">
                                                        <h3>Шаг 1. Регистрация в Binance</h3>
                                                        <ol>
                                                            <li>Перейдите на страницу регистрации Binance: <a href="https://accounts.binance.com/uk-UA/register?ref=GRO_28502_1SCZ1" target="_blank"><b>Зарегистрироваться в Binance</b></a></li>
                                                            <li>Зарегистрируйтесь через Email или телефон</li>
                                                            <li>Подтвердите email / телефон</li>
                                                            <li>Рекомендуется пройти базовую верификацию</li>
                                                        </ol>
                                                        <div class="nav">
                                                            <b class="butt1 pointer"><b><input value="← Назад" type="submit" class="grnn" style="font-size:9px!important" onclick="showStep(0);return false;"></b></b>
                                                            <b class="butt1 pointer"><b><input value="Далее →" type="submit" class="grnn" style="font-size:9px!important" onclick="showStep(2);return false;"></b></b>
                                                        </div>
                                                    </div>
                                                    <div class="step">
                                                        <h3>Шаг 2. Покупка USDT</h3>
                                                        <h4>🇺🇦 Для Украины</h4>
                                                        <p>Binance → <b>P2P</b> → Купить USDT (Monobank / PrivatBank)</p>
                                                        <h4>🇪🇺 Для Европы</h4>
                                                        <p>Binance → <b>Buy Crypto</b> (карта или SEPA)</p>
                                                        <div class="nav">
                                                            <b class="butt1 pointer"><b><input value="← Назад" type="submit" class="grnn" style="font-size:9px!important" onclick="showStep(1);return false;"></b></b>
                                                            <b class="butt1 pointer"><b><input value="Далее →" type="submit" class="grnn" style="font-size:9px!important" onclick="showStep(3);return false;"></b></b>
                                                        </div>
                                                    </div>
                                                    <div class="step">
                                                        <h3>Шаг 3. Перевод USDT</h3>
                                                        <div class="wallet-box">
                                                            <b>Адрес кошелька:</b>
                                                            <div class="wallet">TNvmAvmrjgbmGPDMDSUkzCkxcvkEEJ1YUX <span onclick="copyWallet()">📋</span></div>
                                                            <center><img src="{{ asset('news/bank/crypto1.png') }}" width="250px"></center>
                                                            <p><b>Сеть:</b> TRON (TRC20)<br><b>Валюта:</b> USDT</p>
                                                            <div class="warn">⚠️ Выбор другой сети приведёт к потере средств</div>
                                                        </div>
                                                        <div class="nav">
                                                            <b class="butt1 pointer"><b><input value="← Назад" type="submit" class="grnn" style="font-size:9px!important" onclick="showStep(2);return false;"></b></b>
                                                            <b class="butt1 pointer"><b><input value="Далее →" type="submit" class="grnn" style="font-size:9px!important" onclick="showStep(4);return false;"></b></b>
                                                        </div>
                                                    </div>
                                                    <div class="step">
                                                        <h3>Готово</h3>
                                                        <p>После оплаты напишите администратору <b>Aizen&nbsp;[12]</b> на внутреннюю почту игры (справа), или свяжитесь с нами в Discord: <b>aizenukraine</b>.</p>
                                                        <p><b>После проверки транзакции средства будут зачислены.</b></p>
                                                        <div class="nav"><b class="butt1 pointer"><b><input value="← Назад" type="submit" class="grnn" style="font-size:9px!important" onclick="showStep(3);return false;"></b></b></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <center>
                                            Вам нужно выбрать монету USDT, нажать кнопку "вывод", затем ввести мой кошелёк TNvmAvmrjgbmGPDMDSUkzCkxcvkEEJ1YUX и выбрать сеть TRX Tron TRC20<br><br>
                                            <img src="{{ asset('news/2023/crypto1.png') }}" width="400px"><br><br>
                                            <img src="{{ asset('news/2023/crypto2.png') }}" width="400px">
                                            </center>

                                            </div>
                                            </div>
                                            </td></tr>
                                            </table>
                                            </center>
                                        </td>
                                        <td width="12" class="common-inset-r"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                    </tr>
                                    <tr>
                                        <td width="12" height="12" class="common-inset-bl"></td>
                                        <td height="12" class="common-inset-b"></td>
                                        <td width="12" height="12" class="common-inset-br"></td>
                                    </tr>
                                    </table>
                                </div>
                                </td>
                                <td class="common-inner-block-r"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                            </tr>
                            <tr>
                                <td width="10" height="10" class="common-inner-block-bl"></td>
                                <td height="10" class="common-inner-block-b"></td>
                                <td width="10" height="10" class="common-inner-block-br"></td>
                            </tr>
                            </table>
                            {{-- === /BLOCK 2 === --}}

                            {{-- === BLOCK 3: Volet + Diamond Calc === --}}
                            <table class="w100 jqSet" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td class="common-inner-block-tl" width="10" height="10"></td>
                                <td class="common-inner-block-t" height="10"></td>
                                <td class="common-inner-block-tr" width="10" height="10"></td>
                            </tr>
                            <tr>
                                <td class="common-inner-block-l" width="10"></td>
                                <td class="common-inner-block-bg">
                                <div class="common-inner-block__mh">
                                    <table class="w100" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td class="common-inset-tl" width="12" height="12"></td>
                                        <td class="common-inset-t" height="12"></td>
                                        <td class="common-inset-tr" width="12" height="12"></td>
                                    </tr>
                                    <tr>
                                        <td class="common-inset-l" width="12"></td>
                                        <td class="common-inset-bg">
                                            <div class="premium-features-text" style="text-align:center">

                                                <div id="dwar-calc-box">
                                                    <div class="calc-title">💎 Калькулятор алмазов</div>
                                                    <div class="calc-box-inner">
                                                        <p>Курс: <b>1 USD = 6.66 алмазов</b></p>
                                                        <p>Введите сумму в USD:
                                                            <input type="number" min="1" step="0.01" id="usdInput" class="calc-input" oninput="calculateDiamonds()">
                                                        </p>
                                                        <div class="calc-result">Вы получите: <span id="diamondResult">0</span> 💎</div>
                                                        <div class="warn">⚠ Расчёт приблизительный</div>
                                                    </div>
                                                </div>

                                                <div id="dwar-volet-box">
                                                    <div class="dwar-steps">
                                                        <span class="active" onclick="showStepVolet(0)">Общее</span>
                                                        <span onclick="showStepVolet(1)">Шаг 1</span>
                                                        <span onclick="showStepVolet(2)">Шаг 2</span>
                                                        <span onclick="showStepVolet(3)">Шаг 3</span>
                                                        <span onclick="showStepVolet(4)">Перевод</span>
                                                    </div>
                                                    <div class="dwar-content">
                                                        <div class="step active">
                                                            <h3>Оплата через Volet</h3>
                                                            <p>Пополнение счёта через систему <b>Volet</b> — быстрый и удобный способ оплаты банковской картой.</p>
                                                            <ul>
                                                                <li>✔ Поддержка Visa / Mastercard</li>
                                                                <li>✔ Работает по всему миру</li>
                                                                <li>✔ Зачисление мгновенное</li>
                                                                <li>✔ Комиссия ~3.5%</li>
                                                            </ul>
                                                            <div class="nav"><b class="butt1 pointer"><b><input value="Далее →" type="submit" class="grnn" style="font-size:9px!important" onclick="showStepVolet(1);return false;"></b></b></div>
                                                        </div>
                                                        <div class="step">
                                                            <h3>Шаг 1. Регистрация</h3>
                                                            <ol>
                                                                <li>Перейдите на сайт: <a href="https://account.volet.com:443/referral/8cce2ca1-d326-45bd-a9fa-be6816887801" target="_blank"><b>account.volet.com</b></a></li>
                                                                <li>Нажмите "Регистрация"</li>
                                                                <li>Укажите Email и пароль</li>
                                                                <li>Подтвердите email</li>
                                                            </ol>
                                                            <div class="nav">
                                                                <b class="butt1 pointer"><b><input value="← Назад" type="submit" class="grnn" style="font-size:9px!important" onclick="showStepVolet(0);return false;"></b></b>
                                                                <b class="butt1 pointer"><b><input value="Далее →" type="submit" class="grnn" style="font-size:9px!important" onclick="showStepVolet(2);return false;"></b></b>
                                                            </div>
                                                        </div>
                                                        <div class="step">
                                                            <h3>Шаг 2. Верификация</h3>
                                                            <ol>
                                                                <li>Войдите в профиль</li>
                                                                <li>Перейдите в раздел "Верификация"</li>
                                                                <li>Загрузите паспорт или ID</li>
                                                                <li>Сделайте селфи (по запросу)</li>
                                                            </ol>
                                                            <div class="warn">⚠ Без верификации пополнение может быть недоступно</div>
                                                            <div class="nav">
                                                                <b class="butt1 pointer"><b><input value="← Назад" type="submit" class="grnn" style="font-size:9px!important" onclick="showStepVolet(1);return false;"></b></b>
                                                                <b class="butt1 pointer"><b><input value="Далее →" type="submit" class="grnn" style="font-size:9px!important" onclick="showStepVolet(3);return false;"></b></b>
                                                            </div>
                                                        </div>
                                                        <div class="step">
                                                            <h3>Шаг 3. Пополнение картой</h3>
                                                            <ol>
                                                                <li>Выберите "Пополнение счета"</li>
                                                                <li>Выберите USD</li>
                                                                <li>Выберите Visa или Mastercard</li>
                                                                <li>Введите сумму</li>
                                                                <li>Подтвердите платёж</li>
                                                            </ol>
                                                            <div class="nav">
                                                                <b class="butt1 pointer"><b><input value="← Назад" type="submit" class="grnn" style="font-size:9px!important" onclick="showStepVolet(2);return false;"></b></b>
                                                                <b class="butt1 pointer"><b><input value="Далее →" type="submit" class="grnn" style="font-size:9px!important" onclick="showStepVolet(4);return false;"></b></b>
                                                            </div>
                                                        </div>
                                                        <div class="step">
                                                            <h3>Шаг 4. Перевод средств</h3>
                                                            <div class="wallet-box">
                                                                <b>Номер кошелька:</b>
                                                                <div class="wallet">U 8552 8640 8009 <span onclick="copyVoletWallet()">📋</span></div>
                                                                <p>В разделе <b>"Перевод средств"</b><br>укажите номер кошелька и сумму перевода.</p>
                                                                <div class="warn">⚠ Внимательно проверяйте номер перед подтверждением</div>
                                                            </div>
                                                            <p>После перевода сообщите администратору <b>Aizen&nbsp;[12]</b> для быстрого зачисления.</p>
                                                            <div class="nav"><b class="butt1 pointer"><b><input value="← Назад" type="submit" class="grnn" style="font-size:9px!important" onclick="showStepVolet(3);return false;"></b></b></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <b style="color:red;">При желании вы можете пополнить счёт другому игроку, указав его ID</b><br>
                                                <b style="color:green;">Начисление на баланс: от 5 мин до 6 часов<br>Рабочее время: с 09:00 до 23:00</b><br>
                                                В другое время лучше не пополнять, если не хотите ждать до утра
                                                <br><br>

                                            </div>
                                        </td>
                                        <td class="common-inset-r" width="12"></td>
                                    </tr>
                                    <tr>
                                        <td class="common-inset-bl" width="12" height="12"></td>
                                        <td class="common-inset-b" height="12"></td>
                                        <td class="common-inset-br" width="12" height="12"></td>
                                    </tr>
                                    </table>
                                </div>
                                </td>
                                <td class="common-inner-block-r" width="10"></td>
                            </tr>
                            <tr>
                                <td class="common-inner-block-bl" width="10" height="10"></td>
                                <td class="common-inner-block-b" height="10"></td>
                                <td class="common-inner-block-br" width="10" height="10"></td>
                            </tr>
                            </table>
                            {{-- === /BLOCK 3 === --}}

                            {{-- === BLOCK 4: PayPal === --}}
                            <table border="0" cellpadding="0" cellspacing="0" class="w100 jqSet">
                            <tr>
                                <td width="10" height="10" class="common-inner-block-tl"></td>
                                <td height="10" class="common-inner-block-t"></td>
                                <td width="10" height="10" class="common-inner-block-tr"></td>
                            </tr>
                            <tr>
                                <td class="common-inner-block-l"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                                <td class="common-inner-block-bg">
                                <div class="common-inner-block__mh">
                                    <table class="w100" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="12" height="12" class="common-inset-tl"></td>
                                        <td height="12" class="common-inset-t"></td>
                                        <td width="12" height="12" class="common-inset-tr"></td>
                                    </tr>
                                    <tr>
                                        <td width="12" class="common-inset-l"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                        <td height="12" class="common-inset-bg">
                                            <center>
                                            <table class="premium-features">
                                            <tr><td width="50%">
                                            <div class="clearfix">
                                            <div class="premium-features-text" style="text-align:center">

                                                <div id="dwar-calc-box2">
                                                    <div class="calc-title">💎 Калькулятор алмазов</div>
                                                    <div class="calc-box-inner">
                                                        <p>Курс: <b>1 USD = 6.66 алмазов</b></p>
                                                        <p>Введите сумму в USD:
                                                            <input type="number" min="1" step="0.01" id="usdInput2" class="calc-input" oninput="calculateDiamonds2()">
                                                        </p>
                                                        <div class="calc-result">Вы получите: <span id="diamondResult2">0</span> 💎</div>
                                                        <div class="warn">⚠ Расчёт приблизительный</div>
                                                    </div>
                                                </div>

                                                <p><b style="color:#B22222;font-size:15px;">Ваш ID: </b><b style="color:#FF0000;font-size:15px;">{{ $user->id }}</b></p>
                                                <br>
                                                <form action="https://www.paypal.com/donate" method="post" target="_blank">
                                                    <input type="hidden" name="hosted_button_id" value="UUYDLSK4FKDY6">
                                                    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button">
                                                </form>
                                                <b style="color:red;">При желании вы можете пополнить счёт другому игроку, указав его ID</b><br>
                                                <b style="color:green;">Начисление на баланс: от 5 мин до 6 часов<br>Рабочее время: с 09:00 до 23:00</b><br>
                                                В другое время лучше не пополнять, если не хотите ждать до утра
                                                <br><br>

                                            </div>
                                            </div>
                                            </td></tr>
                                            </table>
                                            </center>
                                        </td>
                                        <td width="12" class="common-inset-r"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                    </tr>
                                    <tr>
                                        <td width="12" height="12" class="common-inset-bl"></td>
                                        <td height="12" class="common-inset-b"></td>
                                        <td width="12" height="12" class="common-inset-br"></td>
                                    </tr>
                                    </table>
                                </div>
                                </td>
                                <td class="common-inner-block-r"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                            </tr>
                            <tr>
                                <td width="10" height="10" class="common-inner-block-bl"></td>
                                <td height="10" class="common-inner-block-b"></td>
                                <td width="10" height="10" class="common-inner-block-br"></td>
                            </tr>
                            </table>
                            {{-- === /BLOCK 4 === --}}

                            {{-- === BLOCK 5: Супер-удар === --}}
                            <table border="0" cellpadding="0" cellspacing="0" class="w100 jqSet">
                            <tr>
                                <td width="10" height="10" class="common-inner-block-tl"></td>
                                <td height="10" class="common-inner-block-t"></td>
                                <td width="10" height="10" class="common-inner-block-tr"></td>
                            </tr>
                            <tr>
                                <td class="common-inner-block-l"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                                <td class="common-inner-block-bg">
                                <div class="common-inner-block__mh">
                                    <table class="w100" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="12" height="12" class="common-inset-tl"></td>
                                        <td height="12" class="common-inset-t"></td>
                                        <td width="12" height="12" class="common-inset-tr"></td>
                                    </tr>
                                    <tr>
                                        <td width="12" class="common-inset-l"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                        <td height="12" class="common-inset-bg">
                                            <center>
                                            <table class="premium-features">
                                            <tr><td width="50%">
                                            <div class="clearfix">
                                            <div class="premium-features-text" style="text-align:center">
                                                <b class="premium-features-header">Уникальный супер-удар.</b>
                                                <p align="center"><b style="color:darkgreen;font-size:15px;">Стоимость:</b><b style="font-size:15px;"> - 100 <img align="absmiddle" src="{{ asset('img/icon/m_dmd.gif') }}"></b></p>
                                                Каждый игрок имеет право заказать себе <b>уникальный супер-удар</b> <br>
                                                Эта услуга позволяет игроку выбрать последовательность ударов в бою для срабатывания супер-удара <br>
                                                Цена указана за изменение только <b>одного комбо</b> <br>
                                                Вы можете сделать <b>до 5 супер-ударов</b>
                                                <br><br>
                                                <img src="{{ asset('news/2023/combo1.png') }}">
                                                <br><img src="{{ asset('main/images/data/smiles/hero.gif') }}">
                                                <br>
                                                <b class="butt1 pointer"><b><input value="Заказать!" type="button"></b></b>
                                            </div>
                                            </div>
                                            </td></tr>
                                            </table>
                                            </center>
                                        </td>
                                        <td width="12" class="common-inset-r"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                    </tr>
                                    <tr>
                                        <td width="12" height="12" class="common-inset-bl"></td>
                                        <td height="12" class="common-inset-b"></td>
                                        <td width="12" height="12" class="common-inset-br"></td>
                                    </tr>
                                    </table>
                                </div>
                                </td>
                                <td class="common-inner-block-r"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                            </tr>
                            <tr>
                                <td width="10" height="10" class="common-inner-block-bl"></td>
                                <td height="10" class="common-inner-block-b"></td>
                                <td width="10" height="10" class="common-inner-block-br"></td>
                            </tr>
                            </table>
                            {{-- === /BLOCK 5 === --}}

                            {{-- === BLOCK 6: Увеличение рюкзака === --}}
                            <table border="0" cellpadding="0" cellspacing="0" class="w100 jqSet">
                            <tr>
                                <td width="10" height="10" class="common-inner-block-tl"></td>
                                <td height="10" class="common-inner-block-t"></td>
                                <td width="10" height="10" class="common-inner-block-tr"></td>
                            </tr>
                            <tr>
                                <td class="common-inner-block-l"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                                <td class="common-inner-block-bg">
                                <div class="common-inner-block__mh">
                                    <table class="w100" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="12" height="12" class="common-inset-tl"></td>
                                        <td height="12" class="common-inset-t"></td>
                                        <td width="12" height="12" class="common-inset-tr"></td>
                                    </tr>
                                    <tr>
                                        <td width="12" class="common-inset-l"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                        <td height="12" class="common-inset-bg">
                                            <center>
                                            <table class="premium-features">
                                            <tr><td width="50%">
                                            <div class="premium-features-text" style="text-align:center">
                                                <b class="premium-features-header">Увеличение рюкзака.</b>
                                                <p align="center"><b style="color:darkgreen">Цена:</b> <br>
                                                <b>+25 мест - 150 <img align="absmiddle" src="{{ asset('img/icon/m_dmd.gif') }}"></b><br>
                                                <b>+50 мест - 285 <img align="absmiddle" src="{{ asset('img/icon/m_dmd.gif') }}"></b><br>
                                                <b>+100 мест - 525 <img align="absmiddle" src="{{ asset('img/icon/m_dmd.gif') }}"></b><br>
                                                <br>Услугу можно купить только <b>2 раза</b><br>
                                                Увеличение работает на <b>постоянной основе</b><br>
                                                <br>Это гораздо выгоднее, чем <b>покупать рюкзак на 30 дней</b>
                                                <br>Каждый игрок имеет право заказать себе услугу по увеличению размера своего рюкзака.<br><br>
                                                <b style="color:darkred">... Размер имеет значение ...</b></p>
                                                <br>
                                                <img src="{{ asset('main/images/data/area_store_category/122803_3_3653_131546_1.png') }}">
                                                <br>
                                                <b class="butt1 pointer"><b><input value="Заказать!" type="button"></b></b>
                                            </div>
                                            </td></tr>
                                            </table>
                                            </center>
                                        </td>
                                        <td width="12" class="common-inset-r"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                    </tr>
                                    <tr>
                                        <td width="12" height="12" class="common-inset-bl"></td>
                                        <td height="12" class="common-inset-b"></td>
                                        <td width="12" height="12" class="common-inset-br"></td>
                                    </tr>
                                    </table>
                                </div>
                                </td>
                                <td class="common-inner-block-r"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                            </tr>
                            <tr>
                                <td width="10" height="10" class="common-inner-block-bl"></td>
                                <td height="10" class="common-inner-block-b"></td>
                                <td width="10" height="10" class="common-inner-block-br"></td>
                            </tr>
                            </table>
                            {{-- === /BLOCK 6 === --}}

                            {{-- === BLOCK 7: Клановый смайлик === --}}
                            <table border="0" cellpadding="0" cellspacing="0" class="w100 jqSet">
                            <tr>
                                <td width="10" height="10" class="common-inner-block-tl"></td>
                                <td height="10" class="common-inner-block-t"></td>
                                <td width="10" height="10" class="common-inner-block-tr"></td>
                            </tr>
                            <tr>
                                <td class="common-inner-block-l"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                                <td class="common-inner-block-bg">
                                <div class="common-inner-block__mh">
                                    <table class="w100" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="12" height="12" class="common-inset-tl"></td>
                                        <td height="12" class="common-inset-t"></td>
                                        <td width="12" height="12" class="common-inset-tr"></td>
                                    </tr>
                                    <tr>
                                        <td width="12" class="common-inset-l"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                        <td height="12" class="common-inset-bg">
                                            <center>
                                            <table class="premium-features">
                                            <tr><td width="50%">
                                            <div class="clearfix">
                                            <div class="premium-features-text" style="text-align:center">
                                                <b class="premium-features-header">Клановый смайлик.</b>
                                                <p align="center"><b style="color:darkgreen;font-size:15px;">Цена:</b><b style="font-size:15px;"> 200 <img align="absmiddle" src="{{ asset('img/icon/m_dmd.gif') }}"></b></p>
                                                <br>Смайлик, который смогут использовать <b>все члены клана</b>.<br>
                                                <br>
                                                <img src="{{ asset('main/images/data/smiles/kl_kazna.gif') }}">
                                                <br>
                                                <b class="butt1 pointer"><b><input value="Заказать!" type="button"></b></b>
                                            </div>
                                            </div>
                                            </td></tr>
                                            </table>
                                            </center>
                                        </td>
                                        <td width="12" class="common-inset-r"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                    </tr>
                                    <tr>
                                        <td width="12" height="12" class="common-inset-bl"></td>
                                        <td height="12" class="common-inset-b"></td>
                                        <td width="12" height="12" class="common-inset-br"></td>
                                    </tr>
                                    </table>
                                </div>
                                </td>
                                <td class="common-inner-block-r"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                            </tr>
                            <tr>
                                <td width="10" height="10" class="common-inner-block-bl"></td>
                                <td height="10" class="common-inner-block-b"></td>
                                <td width="10" height="10" class="common-inner-block-br"></td>
                            </tr>
                            </table>
                            {{-- === /BLOCK 7 === --}}

                            </div>{{-- /set-tabs-content --}}

                        </td>
                        </tr></tbody></table>
                        </center>

                    </td>
                    <td class="tbl-shp-sides rs">&nbsp;</td>
                </tr>
                <tr height="18">
                    <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
                    <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
                    <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
                </tr>
                </table>
            </td>
            {{-- /RIGHT --}}

        </tr>
        </table>

    </td>
</tr>
</table>

</td></tr>
</table>

<script>
var max_amount = 30000;
function calc_exchange(from, to, mode, op, exchange_ratio) {
    var el = document.getElementById(from);
    var el1 = document.getElementById(to);
    var value = el.value.replace(/[^0-9\.]/gi, '').replace(/[\.]+/gi, '.').replace(/^0+/, '0').replace(/^0([^\.]+)/, '$1');
    value = value ? value : 0;
    op = op > 0 ? 1 : (op < 0 ? -1 : 0);
    if (op === 0) {
        if (value && value.search(/\./) > 0 && value.length - value.search(/\./) > 3) {
            value = parseFloat(value).toFixed(2);
        }
        var tmp = Math.floor(value);
        if (mode) {
            value = tmp > max_amount * exchange_ratio ? (max_amount * exchange_ratio).toFixed(2) : value;
        } else {
            value = tmp > max_amount ? max_amount : value;
        }
    } else {
        value = Math.floor(value);
        value = Math.min(mode ? (max_amount * exchange_ratio).toFixed(2) : max_amount, Math.max(0, value + op));
    }
    el.value = value.toString().replace(/\.00$/ig, '');
    var value1 = ((mode ? value / exchange_ratio : value * exchange_ratio) || 0).toFixed(2);
    el1.value = value1.toString().replace(/\.00$/ig, '');
}

var checkbox = document.getElementById('agree-checkbox');
var submitButton = document.getElementById('submit-button');
var tooltipText = document.getElementById('tooltip-text');
checkbox.addEventListener('change', function() {
    submitButton.disabled = !this.checked;
    tooltipText.style.visibility = this.checked ? 'hidden' : 'visible';
});
document.getElementById('top-up-form').addEventListener('submit', function(e) {
    e.preventDefault();
    if (!document._submit) {
        document._submit = true;
        window.open('https://prt.mn/XV8dOuwBv2', '_blank');
    }
});

function showStep(n) {
    var steps = document.querySelectorAll('#dwar-usdt-box .step');
    var tabs  = document.querySelectorAll('#dwar-usdt-box .dwar-steps span');
    steps.forEach(function(s){ s.classList.remove('active'); });
    tabs.forEach(function(t){ t.classList.remove('active'); });
    steps[n].classList.add('active');
    tabs[n].classList.add('active');
}
function copyWallet() {
    navigator.clipboard.writeText('TNvmAvmrjgbmGPDMDSUkzCkxcvkEEJ1YUX');
    alert('Адрес кошелька скопирован');
}
function copyVoletWallet() {
    navigator.clipboard.writeText('U 8552 8640 8009');
    alert('Номер кошелька скопирован');
}
function showStepVolet(n) {
    var steps = document.querySelectorAll('#dwar-volet-box .step');
    var tabs  = document.querySelectorAll('#dwar-volet-box .dwar-steps span');
    steps.forEach(function(s){ s.classList.remove('active'); });
    tabs.forEach(function(t){ t.classList.remove('active'); });
    steps[n].classList.add('active');
    tabs[n].classList.add('active');
}
function calculateDiamonds() {
    var usd = parseFloat(document.getElementById('usdInput').value) || 0;
    document.getElementById('diamondResult').innerText = (usd * 6.66).toFixed(2);
}
function calculateDiamonds2() {
    var usd = parseFloat(document.getElementById('usdInput2').value) || 0;
    document.getElementById('diamondResult2').innerText = (usd * 6.66).toFixed(2);
}

// jqSet tab switching (same behaviour as prod jQuery snippet)
(function() {
    var blocks = document.querySelectorAll('.jqSet');
    var tabs   = document.querySelectorAll('.set-tabs .tab');
    var setTabs = document.querySelector('.set-tabs');
    if (setTabs) {
        document.querySelectorAll('.common-inner-block__mh').forEach(function(el) {
            el.style.minHeight = (setTabs.offsetHeight - 20) + 'px';
        });
    }
    blocks.forEach(function(b){ b.style.display = 'none'; });
    if (blocks.length) blocks[0].style.display = '';
    tabs.forEach(function(tab, i) {
        tab.addEventListener('click', function() {
            blocks.forEach(function(b){ b.style.display = 'none'; });
            tabs.forEach(function(t){ t.classList.remove('tab-active'); });
            if (blocks[i]) blocks[i].style.display = '';
            this.classList.add('tab-active');
        });
    });
})();
</script>

</body>
</html>
