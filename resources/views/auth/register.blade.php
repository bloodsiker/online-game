<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <title>New Online game</title>

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="DWAR Легенда: Спадок драконів">
    <meta name="twitter:description" content="Офіційний український сервер Двар Легенда: Спадок драконів">
    <meta name="twitter:image" content="/new_design/img/logo_tshare.png">
    <meta name="twitter:domain" content="dwar.ltd">

    <meta name="description"
          content="Легенда: Спадок драконів, офіційний, український, двар, легенда, український сервер, Бесплатна онлайн гра Двар, Український офіційний сервер легенди, прайм, мінор"/>
    <meta name="keywords"
          content="dwar, маріуполь, АЗОВСТАЛЬ, mariupol, azovstal, український, двар, офіційний, онлайн ігри, ігри онлайн, грати ігри, група, ментори, дракони вічності, браузерна гра, mmorpg, дракони, магмари, арена, apeha, online, двар, прайм, мінор"/>
    <meta property="og:title" content="Гра Легенда спадок драконів Український сервер"/>
    <meta property="og:image" content="/new_design/img/logo_tshare.png"/>
    <meta property="og:type" content="article"/>
    <meta property="og:url" content="https://dwar.ltd/"/>
    <meta property="og:site_name" content="Онлайн гра двар легенда спадок драконів"/>
    <meta property="fb:admins" content="1"/>
    <meta property="og:description" content="Український сервер Двар Легенда: Спадок драконів "/>

    <link rel="stylesheet" href="{{ asset('main/css/main_register.css') }}"/>
    <link rel="stylesheet" href="{{ asset('main/css/register.css') }}"/>
    <link rel="stylesheet" href="{{ asset('main/css/index.css') }}"/>

    <link rel="stylesheet" href="{{ asset('main/css/jquery.formstyler.css') }}"/>
    <script src="{{ asset('main/js/jquery.js') }}"></script>
    <script src="{{ asset('main/js/cufon.js') }}"></script>

    <script src="{{ asset('main/js/PTSans_400-PTSans_700.font.js') }}"></script>

    <script src="{{ asset('main/js/jquery.formstyler.min.js') }}"></script>
    <script src="{{ asset('main/js/main_register.js') }}"></script>
    <script src="{{ asset('main/js/common.js') }}"></script>

    <script language="javaScript" src="{{ asset('main/js/simple_alt.js') }}"></script>
    <script language="javaScript" src="{{ asset('main/js/ac_runactivecontent.js') }}"></script>


    <script type="text/javascript">
        function persUpdate(gender, kind, phead, phair, pbody, chair, cbody) {
            var frm = document.forms['reg_form'];
            frm.elements['cfg[gender]'].value = gender;
            frm.elements['cfg[kind]'].value = kind;
            frm.elements['cfg[phead]'].value = phead;
            frm.elements['cfg[phair]'].value = phair;
            frm.elements['cfg[pbody]'].value = pbody;
            frm.elements['cfg[chair]'].value = chair;
            frm.elements['cfg[cbody]'].value = cbody;
        }
    </script>
    <script>
        function is_email_valid(all) {
            var obj = gebi('email');
            if (!obj) return true;
            if (!common_is_email_valid(obj.value, all)) {
                $('#email_field').addClass('error');
                window._submit = true;
                obj.focus();
                return false;
            }
            $('#email_field').removeClass('error');
            window._submit = false;
            return true;
        }

        function is_pass_valid(step) {
            var obj = gebi('Pass');
            var obj2 = gebi('Pass2');
            if (!obj || !obj2) return true;
            if (!obj2.value && !step) return true;
            if (obj.value != obj2.value) {
                $('#passwd_field').addClass('error');
                window._submit = true;
                return false;
            }
            $('#passwd_field').removeClass('error');
            window._submit = false;
            return true;
        }
    </script>
</head>
<body style="background-image: url('{{ asset('main/images/theme_old/bg.gif') }}');">

<link rel="stylesheet" href="{{ asset('main/css/css.css') }}"/>
<link rel="stylesheet" type="text/css" href="{{ asset('main/css/mainnew.css') }}">

<div class="b-main-wrapper">
    <div class="b-main">
        <div class="b-page__header" style="top: 0px;z-index: 5;position: fixed;">
            <header class="b-nav-lvl-1" style=" width: 990px; ">
                <div class="b-nav-lvl-1__decor-border"></div>
                <div class="b-nav-lvl-1__decor-menu-side"></div>
                <div class="b-nav-lvl-1__decor-menu-top"></div>
                <div class="b-nav-lvl-1__auth">
                    <div id="userHeadBlock">
                        <div class="b-nav-lvl-1__create">
                            <a class="b-auth-button" href="{{ route('register') }}">
                                <span class="b-auth-button__content">
                                <span class="b-aside__impo-link" style="font-size: 15px;font-weight: bold;position: relative;color: #d49b2c;left: 1px;top: -3px;">Регистрация</span>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="b-nav-lvl-1__logo">
                    <a class="b-auth-button" style="position: relative;left: 24px;top: 7px;">
                    <span class="b-auth-button__content">
                    <span class="b-aside__impo-link"
                          style="font-size: 15px;font-weight: bold;position: relative;color: #d49b2c;left: 1px;top: -3px;">В игре сейчас: {{ $onlineCount }}</span>
                    </span>
                    </a>
                </div>

                <script>
                    // функция вызова окна
                    function show_popap(id_popap) {
                        var id = "#" + id_popap;
                        $(id).addClass('active');
                    }

                    // функция закрытия окна
                    $(".close_popap").click(function () {
                        $(".overlay").removeClass("active");
                    });
                </script>
                <div class="b-nav-lvl-1__menu">
                    <ul class="b-nav-lvl-1__menu-list">
                        <li class="b-nav-lvl-1__menu-item" style=" list-style-type: none; ">
                            <a class="b-aside__impo-link" href="{{ route('index') }}"
                               style="font-size: 14px;font-weight: bold;position: relative;color: #d49b2c;left: 23px;top: 8px;"><span>Главная</span></a>
                        </li>
                        <li class="b-nav-lvl-1__menu-item" style=" list-style-type: none; ">
                            <a class="b-aside__impo-link" href="#"
                               style="font-size: 14px;font-weight: bold;position: relative;color: #d49b2c;left: 23px;top: 8px;"><span>Библиотека</span></a>
                        </li>
                        <li class="b-nav-lvl-1__menu-item" style=" list-style-type: none; ">
                            <a class="b-aside__impo-link" href="#"
                               style="font-size: 14px;font-weight: bold;position: relative;color: #d49b2c;left: 31px;top: 8px;"><span>Телеграм</span></a>
                        </li>
                        <li class="b-nav-lvl-1__menu-item" style=" list-style-type: none; ">
                            <a class="b-aside__impo-link" href="#"
                               style="font-size: 14px;font-weight: bold;position: relative;color: #d49b2c;left: 24px;top: 8px;"><span>Контакты</span></a>
                        </li>
                    </ul>
                </div>
            </header>


        </div>


        <div class="b-column-wrapper" style="width:860px;position: relative;top: -40px;">
            <div class="b-main-frame">
                <span class="b-main-frame__l"></span>
                <span class="b-main-frame__r"></span>
                <span class="b-main-frame__t"></span>
                <span class="b-main-frame__b"></span>

                <span class="b-main-frame__decor-tl"></span>
                <span class="b-main-frame__decor-tl-2"></span>

                <span class="b-main-frame__decor-tr"></span>
                <span class="b-main-frame__decor-tr-2"></span>

                <span class="b-main-frame__decor-bl"></span>
                <span class="b-main-frame__decor-br"></span>

                <div class="b-main-frame__cont">
                    <div class="b-common-block">
                        <span class="b-common-block__l"></span>
                        <span class="b-common-block__r"></span>
                        <span class="b-common-block__t b-common-block__t_2"></span>
                        <span class="b-common-block__b"></span>

                        <span class="b-common-block__bl"></span>
                        <span class="b-common-block__br"></span>

                        <span class="b-common-block__header-decor-l"></span>
                        <span class="b-common-block__header-decor-r"></span>

                        <span class="b-common-block__header">
								<span class="b-common-block__header-inner">
									<span data-font="PTSans">REGISTER</span>
								</span>
							</span>

                        <div class="b-common-block__cont">
                            <div class="b-common-block__bgl">
                                <div class="b-common-block__bgr clearfix">
                                    <form id="reg_form" action="{{ route('register') }}" method="post">
                                        <input type="hidden" name="referrer" value=""/>
                                        @csrf
                                        <center>
                                            <div class="b-common-wrapper">
                                                <div class="b-column-wrapper b-column-wrapper_equal clearfix">
                                                    <div class="b-divider-5">
                                                        <div class="b-divider-5__l">
                                                            <div class="b-divider-5__l-inner"></div>
                                                        </div>

                                                        <div class="b-divider-5__r">
                                                            <div class="b-divider-5__r-inner"></div>
                                                        </div>

                                                        <div class="b-divider-5__c"></div>
                                                    </div>
                                                    <h2 class="b-common-header">Ваши данные</h2>

                                                    <p class="b-common-form__description">
                                                        <b>Ник персонажа</b> - это имя, под которым Вас будут знать все игроки и администрация.<br>
                                                        Не используйте нецензурные и провокационные слова. Допустимо от 2 букв или цифр до 16. </p>

                                                    <p class="b-user-data-description">
                                                        <b>E-mail</b> - нужно вводить <b>при входе в игру</b>.
                                                    </p>

                                                    <div class="b-divider-5 b-divider-5_no-center">
                                                        <div class="b-divider-5__l">
                                                            <div class="b-divider-5__l-inner"></div>
                                                        </div>

                                                        <div class="b-divider-5__r">
                                                            <div class="b-divider-5__r-inner"></div>
                                                        </div>
                                                    </div>

{{--                                                    <div class="b-news-item">--}}
{{--                                                        <div class="b-news-item__header clearfix">--}}
{{--                                                            <div id="nick_ex" class="b-news-footer" style="">--}}
{{--                                                                <span class="b-news-footer__t"></span>--}}
{{--                                                                <span class="b-news-footer__b"></span>--}}

{{--                                                                <span class="b-news-footer__ct"></span>--}}
{{--                                                                <span class="b-news-footer__cb"></span>--}}

{{--                                                                <span class="b-news-footer__tl"></span>--}}
{{--                                                                <span class="b-news-footer__tr"></span>--}}
{{--                                                                <span class="b-news-footer__bl"></span>--}}
{{--                                                                <span class="b-news-footer__br"></span>--}}
{{--                                                                <div class="b-news-footer__cont clearfix">--}}
{{--                                                                    <div id="nick_examples" style="" class="tbl-reg_bg-light tbl-reg_brd-all"><b class="tbl-reg_error">Ник занят. Введите новый или выберите из предложенных:</b><br><div>1. <a href="#" class="change_nick" onclick="change_nick('BlooDSikeR1', 0); return false;">BlooDSikeR1</a></div><div>2. <a href="#" class="change_nick" onclick="change_nick('-BlooDSikeR-', 1); return false;">-BlooDSikeR-</a></div><div>3. <a href="#" class="change_nick" onclick="change_nick('_BlooDSikeR_', 2); return false;">_BlooDSikeR_</a></div></div>--}}
{{--                                                                </div>--}}

{{--                                                            </div></div>--}}
{{--                                                    </div>--}}

                                                    @if ($errors->any())
                                                        <div class="tbl8" style="margin: 5px 0px 5px 0px;" align="left">
                                                            <div style="text-align: justify;font-size: 11px;">
                                                                <p></p>
                                                                <center>
                                                                    <ul style="list-style-type: none;;">
                                                                        @foreach ($errors->all() as $error)
                                                                         <li style="color: red;">{{ $error }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </center>
                                                                <p></p>
                                                            </div>
                                                        </div>

                                                        <div class="b-divider-5 b-divider-5_no-center">
                                                            <div class="b-divider-5__l">
                                                                <div class="b-divider-5__l-inner"></div>
                                                            </div>

                                                            <div class="b-divider-5__r">
                                                                <div class="b-divider-5__r-inner"></div>
                                                            </div>
                                                        </div>
                                                    @endif

{{--                                                    @if ($errors->any())--}}

{{--                                                        <div class="alert alert-danger">--}}
{{--                                                           --}}
{{--                                                        </div>--}}

{{--                                                        <div class="b-divider-5 b-divider-5_no-center">--}}
{{--                                                            <div class="b-divider-5__l">--}}
{{--                                                                <div class="b-divider-5__l-inner"></div>--}}
{{--                                                            </div>--}}

{{--                                                            <div class="b-divider-5__r">--}}
{{--                                                                <div class="b-divider-5__r-inner"></div>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    @endif--}}

                                                    <script>
                                                        var alternative_nicks = [];

                                                        window.onload = function () {
                                                            if ($('#nick').val()) check_nick();
                                                        }

                                                        function check_nick() {
                                                            $('#nick_examples').removeClass('tbl-reg_bg-light').removeClass('tbl-reg_brd-all').html('');
                                                            if ($('#nick').val().length && ($('#nick').val().length < 2 || $('#nick').val().length > 16)) {
                                                                $('#nick_examples').addClass('tbl-reg_bg-light').addClass('tbl-reg_brd-all').append(
                                                                    $('<b/>').addClass('tbl-reg_error').append('Длина ника должна быть от 2 до 16 символов.')
                                                                ).append($('<br/>'));
                                                                return;
                                                            }
                                                            $.getJSON('{{ route('register.check') }}', {nick: $('#nick').val()}, function (result) {
                                                                $('#nick_examples').html('');
                                                                alternative_nicks = [];
                                                                if (result !== 0) {
                                                                    if (result[0].length || result[1].length || result[2].length) {
                                                                        $('#nick_examples').addClass('tbl-reg_bg-light').addClass('tbl-reg_brd-all').append(
                                                                            $('<b/>').addClass('tbl-reg_error').append('Ник занят. Введите новый или выберите из предложенных:')
                                                                        ).append($('<br/>'));
                                                                        $('#nick_ex').show();
                                                                    }
                                                                    alternative_nicks = result;
                                                                    var n = 1;
                                                                    for (var cur_type = 0; cur_type < 3; cur_type++) {
                                                                        if (!result[cur_type].length) continue;
                                                                        var count = 1;
                                                                        for (var type = 0; type < 3; type++) {
                                                                            if (cur_type != type && !result[type].length) count++;
                                                                        }
                                                                        count = count - n + 1;
                                                                        if (count < 1) count = 1;
                                                                        for (var i = 0; i < count; i++) {
                                                                            var nick = result[cur_type][i];
                                                                            $('#nick_examples').append(
                                                                                $('<div/>').html(n + '. <a href="register.html#" class="change_nick" onclick="change_nick(\'' + nick + '\', ' + cur_type + '); return false;">' + nick + '</a>')
                                                                            );
                                                                            n++;
                                                                        }
                                                                    }
                                                                }
                                                            });
                                                            check_next();
                                                        }

                                                        function change_nick(nick, change_type) {
                                                            var temp_alternative_nicks = alternative_nicks[change_type];
                                                            alternative_nicks[change_type] = [];
                                                            for (var i = 0; i < temp_alternative_nicks.length; i++) {
                                                                if (temp_alternative_nicks[i] != nick) alternative_nicks[change_type].push(temp_alternative_nicks[i]);
                                                            }
                                                            alternative_nicks[change_type].push(nick);
                                                            $('#nick').val(nick);
                                                            $('#nick_examples').removeClass('tbl-reg_bg-light').removeClass('tbl-reg_brd-all').html('');
                                                            var n = 1;
                                                            for (var cur_type = 0; cur_type < 3; cur_type++) {
                                                                if (!alternative_nicks[cur_type].length) continue;
                                                                var count = 1;
                                                                for (var type = 0; type < 3; type++) {
                                                                    if (cur_type != type && !alternative_nicks[type].length) count++;
                                                                }
                                                                count = count - n + 1;
                                                                if (count < 1) count = 1;
                                                                for (var i = 0; i < count; i++) {
                                                                    nick = alternative_nicks[cur_type][i];
                                                                    $('#nick_examples').addClass('tbl-reg_bg-light').addClass('tbl-reg_brd-all').append(
                                                                        $('<div/>').html(n + '. <a href="register.html#" class="change_nick" onclick="change_nick(\'' + nick + '\', ' + cur_type + '); return false;">' + nick + '</a>')
                                                                    );
                                                                    n++;
                                                                }
                                                            }
                                                        }
                                                    </script>
                                                    <style>
                                                        .tbl8 {
                                                            background: #fdffde;
                                                            border: 1px dotted #4d3522;
                                                            border-radius: 2px;
                                                            padding: 5px;
                                                        }
                                                    </style>
                                                    <table class="b-common-form__table">
                                                        <tr>
                                                            <td>
                                                                <label for="nick" class="b-common-form__label">Ник персонажа:</label>
                                                            </td>
                                                            <td>
															<span class="b-common-form__field">
																<span class="b-common-form__field-inner">
																	<input type="text" name="name" id="nick" onblur="check_nick();" value="{{ old('name') }}"/>
																</span>
															</span>
                                                            </td>
                                                            <td>
                                                                <label for="email" class="b-common-form__label">Ваш E-Mail:</label>
                                                            </td>
                                                            <td>
															<span class="b-common-form__field">
																<span class="b-common-form__field-inner">
																	<input type="text" name="email" id="email" onkeyup="check_next();" value="{{ old('email') }}" onkeyup="is_email_valid()"/>
																</span>
															</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <label for="nick" class="b-common-form__label">Пароль:</label>
                                                            </td>
                                                            <td>
															<span class="b-common-form__field">
																<span class="b-common-form__field-inner">
																	<input type="password" name="password" id="Pass"
                                                                           value="" onfocus="is_email_valid(1);"
                                                                           onkeyup="is_pass_valid();"/>
																</span>
															</span>
                                                            </td>
                                                            <td>
                                                                <label for="email" class="b-common-form__label">Подтвердите пароль:</label>
                                                            </td>
                                                            <td>
															<span class="b-common-form__field">
																<span class="b-common-form__field-inner">
																	<input type="password" name="password_confirmation"
                                                                           id="Pass2" value=""
                                                                           onfocus="is_email_valid(1);"
                                                                           onkeyup="is_pass_valid();"/>
																</span>
															</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <label for="answer" class="b-common-form__label">Раса:</label>
                                                            </td>
                                                            <td>
                                                                <select name="race" id="race" onselect="check_next();" class="tbl-reg_input">
                                                                    @foreach($races as $race)
                                                                        <option value="{{ $race->id }}" @selected(old('race') == $race->id)>{{ $race->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <script>
                                                                    obj = document.getElementById('race');
                                                                    for (i = 0; i < obj.options.length; i++) {
                                                                        if (obj.options[i].text == "") obj.options[i].selected = true;
                                                                    }
                                                                </script>
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <label for="answer" class="b-common-form__label">Пол:</label>
                                                            </td>
                                                            <td>
                                                                <select name="sex" id="sex" onselect="check_next();" class="tbl-reg_input">
                                                                    <option value="male" @selected(old('sex') === 'female')>Мужской</option>
                                                                    <option value="female" @selected(old('sex') === 'female')>Женский</option>
                                                                </select>
                                                                <script>
                                                                    obj = document.getElementById('sex');
                                                                    for (i = 0; i < obj.options.length; i++) {
                                                                        if (obj.options[i].text == "") obj.options[i].selected = true;
                                                                    }
                                                                </script>
                                                            </td>
                                                            <td></td>
                                                            <td></td>
                                                        </tr>
                                                    </table>
                                                    <br>

                                                    <div class="b-divider-5 b-divider-5_invert b-divider-5_no-center">
                                                        <div class="b-divider-5__l">
                                                            <div class="b-divider-5__l-inner"></div>
                                                        </div>

                                                        <div class="b-divider-5__r">
                                                            <div class="b-divider-5__r-inner"></div>
                                                        </div>
                                                    </div>

                                                    <div class="g-recaptcha"
                                                         data-sitekey="6LeytvYkAAAAACe76N7Hsn5TIpBI1nX7VQUgaN5G"></div>
                                                    <script src='https://www.google.com/recaptcha/api.js'></script>

                                                    <p class="text-center">
                                                        <input type="checkbox" name="license" onclick="check_next();" onchange="check_next();"
                                                               id="license" value="1"/> Я принимаю и обязуюсь выполнять условия <a href="#" target="_blank"><b>Лицензионного соглашения</b>
                                                        </a>, <a href="#" target="_blank"><b>Основания для наказаний</b></a> и
                                                        <a href="#" target="_blank"><b>Правила игры</b></a></p>

                                                </div>

                                                <script>
                                                    function check_next() {
                                                        $ch = true;
                                                        if (!$('#nick').val()) $ch = false;
                                                        if (!$('#email').val()) $ch = false;
                                                        if (!$('#license').is(':checked')) $ch = false;
                                                        if (!common_is_email_valid($('#email').val(), 1)) $ch = false;
                                                        var btn = $('#enter_btn');
                                                        if (!$ch) {
                                                            btn.addClass('disabled');
                                                            btn.attr('disabled', 'disabled');
                                                        } else {
                                                            btn.removeClass('disabled');
                                                            btn.removeAttr('disabled');
                                                        }
                                                    }
                                                </script>

                                                <div class="b-divider-4"></div>

                                                <div class="b-common-wrapper clearfix">
{{--                                                    <p class="float-left">--}}
{{--                                                        <a href="https://dwar.ltd/register.php?" class="b-button-red-5">--}}
{{--														<span class="b-button-red-5__inner">--}}
{{--															Предыдущий шаг--}}
{{--                                                        </span>--}}
{{--                                                        </a>--}}
{{--                                                    </p>--}}

                                                    <p class="center">
                                                        <button id="enter_btn" type="submit"
                                                                class="b-button-red-5 disabled" disabled
                                                                onClick="if (is_email_valid(1)) {gebi('reg_form').submit();}; return true;">
														<span class="b-button-red-5__inner">
                                                            Зарегистрироваться
                                                        </span>
                                                        </button>
                                                    </p>
                                                </div>
                                            </div>
                                        </center>
                                    </form>

                                </div>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="b-footer">
            <div class="b-footer__top"></div>


            <div class="b-footer__bottom">
					<span class="b-footer__copyright-block">
						<center>Цей проект не є комерційним.<br>
Проект створено з метою об'єднання людей із загальними інтересами.</center>
					</span>
                &nbsp;
                <span class="b-footer__copyright-block">
						<tr>
	<td class="tbl-mn_menu-bg">
				<img src="images/d.gif" width=6 height=1>

	</td>
</tr>
<tr>
	<td class="tbl-mn_menu-bottom" align="center" valign="top" height="87">
		<div class="wr">



		</div>
	</td>
</tr>
					</span>
            </div>

        </div>
    </div>
</div>
</body>

</html>
