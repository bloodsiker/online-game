<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            background-color: #ffe4aa;
            color: #000;
            font-family: Tahoma;
            font-size: 11px;
        }
        .chat-area {
            width: 100%;
            height: 100%;
            position: relative;
            padding: 5px;
        }
        .message {
            margin-bottom: 1px;
        }
        small {
            font-size: 13px;
        }
        a {
            text-decoration: none;
        }
        .tbl-main_chatchng-link_inact {
            font-weight: bold;
            text-decoration: none;
            color: #5B4736 !important;
        }
        .lgb {
            background-image: url({{ asset('img/bg/lgb.gif') }});
            background-repeat: repeat;
        }
        .lgb-left {
            background-image: url({{ asset('img/icon/lgb-left.gif') }});
            background-repeat: repeat-y;
            width: 14px;
        }
        .lgb-right {
            background-image: url({{ asset('img/icon/lgb-right.gif') }});
            background-repeat: repeat-y;
            width: 15px;
        }


        img {
            vertical-align: middle;
        }
        table[cellpadding="0"][cellpadding="0"][border="0"] {
            border-spacing: 0;
            border-collapse: collapse;
        }
        .tbl-main_chat-left-center {
            background-image: url({{ asset('img/bg/chat/tbl-main_chat-left-center.gif') }});
            background-repeat: repeat-y;
            width: 41px;
        }
        .tbl-main_chat-right-center {
            background-image: url( {{ asset('img/bg/chat/tbl-main_chat-right-center.gif') }});
            background-repeat: repeat-y;
            width: 37px;
        }

        .tbl-main_chat-top {
            background-image: url({{ asset('img/bg/chat/tbl-main_chat-top.gif') }});
            background-repeat: repeat-x;
            height: 35px;
        }
        .tbl-main_chat-top-left {
            background-image: url({{ asset('img/bg/chat/tbl-main_chat-top-left.gif') }});
            background-repeat: no-repeat;
            backgroud-position: left;
            height: 35px;
        }
        .tbl-main_chat-top table.tbl-main_chat-top-left * {
            white-space: nowrap;
        }
        .channel-tabs-container {
            display: inline-block;
            overflow: hidden;
        }
        #channel_tabs {
            height: 30px;
            margin: 5px 0 0 0;
            padding: 0;
            white-space: nowrap;
            font-size: 0;
        }
        #channel_tabs li.channel {
            display: inline-block;
            margin: 0 5px 0 0;
            padding: 0;
            cursor: pointer;
            background: url({{ asset('img/bg/chat/cht-btn-right.png') }}) right top no-repeat;
            font-size: 11px;
        }
        #channel_tabs li.selected {
            background: url({{ asset('img/bg/chat/cht-btn-right-act.png') }}) right top no-repeat;
        }
        #channel_tabs li .settings {
            position: relative;
            display: block;
            width: 27px;
            height: 28px;
            margin: 0;
            float: left;
            cursor: pointer;
            margin-right: -10px;
        }
        #channel_tabs li .settings-color {
            display: block;
            width: 17px;
            height: 16px;
            margin: 7px 0 0 5px;
        }
        #channel_tabs li .settings-cover {
            position: absolute;
            left: 0;
            top: 0;
            width: 27px;
            height: 28px;
            margin: 0;
            background: url({{ asset('img/bg/chat/cht-settings.png') }}) center center no-repeat;
            z-index: 2;
        }
        #channel_tabs li .text {
            float: left;
            margin-right: 13px;
            padding: 8px 5px 10px 15px;
            background: url({{ asset('img/bg/chat/cht-btn-center.png') }}) right top repeat-x;
            color: #5b4736;
            font-size: 11px;
            font-weight: bold;
        }
        #channel_tabs li.selected .text {
            background: url({{ asset('img/bg/chat/cht-btn-center-act.png') }}) right top repeat-x;
            color: #f9dfa1;
        }
        #channel_tabs li.hid {
            display: none;
        }
        .tbl-main_chatchng-ina-c {
            background-image: url({{ asset('img/bg/chat/4_center_inact.png') }});
            background-repeat: repeat-x;
            height: 35px;
            font-family: Tahoma;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            color: #FFE4AA;
            padding-top: 0px;
            text-align: center;
        }
        .tbl-main_chatchng-act-c {
            background-image: url({{ asset('img/bg/chat/4_center_act.png') }});
            background-repeat: repeat-x;
            height: 35px;
            font-family: Tahoma;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            color: #FFE4AA;
            padding-top: 0px;
            text-align: center;
        }

        /*Who*/
        .tbl-main_chat-top-right {
            background: url({{ asset('img/bg/chat/tbl-main_chat-top-right.png') }}) right top repeat-x;
            height: 35px;
        }
        #chat_user_list {
            margin: 0;
            padding: 0 0 0 5px;
            white-space: nowrap;
        }
        #chat_user_list li {
            display: inline-block;
            margin: 0 2px;
            padding: 0;
            clear: both;
            cursor: pointer;
            list-style: none;
            height: 30px;
        }
        #chat_user_list li.selected {
            background: url({{ asset('img/bg/chat/cht-btn-right-act.png') }}) right top no-repeat;
            color: #f9dfa1;
            font-weight: bold;
        }
        #chat_user_list li .icon {
            position: relative;
            display: inline-block;
            width: 29px;
            height: 29px;
            float: left;
            z-index: 2;
        }
        #chat_user_list li.selected .icon {
            margin-right: -10px;
        }
        #chat_user_list li .icon img {
            margin-top: 1px;
        }
        #chat_user_list li.selected .title {
            display: block;
            float: left;
            padding: 8px 5px 9px 10px;
            margin: 0 13px 0 0;
            background: url({{ asset('img/bg/chat/cht-btn-center-act.png') }}) right top repeat-x;
        }
        #chat_user_list li .title {
            display: none;
            padding: 0 5px;
        }
        .tbl-main_separator-v {
            background-image: url({{ asset('img/bg/chat/separator_v.gif') }});
            background-repeat: repeat-y;
            width: 3px;
        }
    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="100%" height="100%" border="0">
    <tbody>
    <tr>
        <td width="100%">
            <table cellpadding="0" cellspacing="0" width="100%" height="100%" border="0">
                <tbody>
                <tr>
                    <td width="41">
                        <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="61">
                                <td><img src="{{ asset('img/bg/chat/tbl-main_chat-left-top.gif') }}" width="41" height="61"></td>
                            </tr>
                            <tr>
                                <td class="tbl-main_chat-left-center">&nbsp;</td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td valign="top">
                        <table width="100%" border="0" height="100%" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="35">
                                <td class="tbl-main_chat-top">
                                    <table width="100%" height="35" border="0" cellspacing="0" cellpadding="0" class="tbl-main_chat-top-left">
                                        <tbody>
                                        <tr height="35">
                                            <td height="35">

                                                <div class="channel-tabs-container" style="width: 100%;">
                                                    <ul id="channel_tabs" title="Каналы чата" class="ui-sortable">
                                                        <li class="moveable channel ui-sortable-handle selected"
                                                            data-channel="main" data-channel-code="1"
                                                            channel-serialize="channel_main"><span
                                                                class="ch settings"><span class="settings-color"
                                                                                          style="background-color: #5A0000;"></span><span
                                                                    class="settings-cover"></span></span><span
                                                                class="text">Основной</span></li>
                                                        <li class="moveable channel ui-sortable-handle"
                                                            data-channel="trade" data-channel-code="8"
                                                            channel-serialize="channel_trade"><span
                                                                class="ch settings"><span
                                                                    class="settings-color"
                                                                    style="background-color: #cc00ff;"></span><span
                                                                    class="settings-cover"></span></span><span
                                                                class="text">Инфо</span></li>
                                                        <li class="moveable channel hid ui-sortable-handle"
                                                            data-channel="party" data-channel-code="16"
                                                            channel-serialize="channel_party" style=""><span
                                                                class="ch settings"><span class="settings-color"
                                                                                          style="background-color: #0033ff;"></span><span
                                                                    class="settings-cover"></span></span><span
                                                                class="text">Группа</span></li>
                                                        <li class="moveable channel ui-sortable-handle"
                                                            data-channel="ally" data-channel-code="64"
                                                            channel-serialize="channel_ally"><span
                                                                class="ch settings"><span class="settings-color"
                                                                                          style="background-color: #009999;"></span><span
                                                                    class="settings-cover"></span></span><span
                                                                class="text">Альянс</span></li>
                                                        <li class="moveable channel ui-sortable-handle"
                                                            data-channel="clan" data-channel-code="4"
                                                            channel-serialize="channel_clan"><span
                                                                class="ch settings"><span class="settings-color"
                                                                                          style="background-color: #007a03;"></span><span
                                                                    class="settings-cover"></span></span><span
                                                                class="text">Клан</span></li>
                                                        <!-- div id="chtMarquee" class="chtip no-settings" style="display: none;"><span class="btn-left" style="position: absolute;width: 13px;height: 29px;"></span><span class="text" style="max-width: 330px;">
<div style="width: 100%;" id="marqueeDiv"></div>
</span></div -->
                                                    </ul>

                                                </div>

                                                <script>
                                                    $channel_tabs = null;
                                                    var channel_list = {};

                                                    function chat_channel_init() {
                                                        if ($channel_tabs === null) $channel_tabs = $('#channel_tabs');
                                                        $channel_tabs.find('li.channel').each(function (i, el) {
                                                            var $el = $(el);
                                                            var channel = $el.data('channel');
                                                            var code = parseInt($el.data('channel-code'));
                                                            $el.attr('channel-serialize', 'channel_' + channel);
                                                            channel_list[code] = $el;
                                                        });
                                                        $channel_tabs.sortable({
                                                            items: "li.channel.moveable",
                                                            revert: 10,
                                                            helper: fixWidthHelper,
                                                            update: function (e, ui) {
                                                                var serialized = $(e.target).sortable('serialize', {attribute: 'channel-serialize'});
                                                                serialized += '&action=tabs_sort';
                                                                $.ajax({
                                                                    url: '/pub/cht_data_save.php',
                                                                    cache: false,
                                                                    type: 'POST',
                                                                    data: serialized,
                                                                    success: function (data) {
                                                                    },
                                                                    error: function () {
                                                                        $(e.target).sortable('cancel');
                                                                    }
                                                                });
                                                            }
                                                        }).disableSelection();
                                                        $channel_tabs.find('li.channel').on('click', chat_channel_click);
                                                        $channel_tabs.find('li .ch.settings').on('click', chat_channel_settings_click);
                                                    };

                                                    function fixWidthHelper(e, ui) {
                                                        ui.children().each(function () {
                                                            $(this).width($(this).width());
                                                        });
                                                        return ui;
                                                    }

                                                    function chat_channel_click(e) {
                                                        var channel = parseInt($(this).data('channel-code'));
                                                        return chatChangePreset(channel);
                                                    };

                                                    function chat_channel_settings_click(e) {
                                                        var $el = $(this);
                                                        var $parent = $el.parent('.channel');
                                                        var code = $parent.data('channel-code');
                                                        showMsg2('/chat_channel_settings.php?channel=' + code, '', 365, 580);
                                                        return false;
                                                    };
                                                    chat_channel_init();

                                                    function chat_channel_settings_save(form, current_channel) {
                                                        _top().error_close();
                                                        $.ajax({
                                                            url: '/pub/cht_data_save.php',
                                                            cache: false,
                                                            type: 'POST',
                                                            data: $(form).serialize()
                                                        }).done(function (json) {
                                                            if (!json) return;
                                                            eval('chat_data = ' + json);
                                                            console.log(json);
                                                            var channel_save = parseInt(chat_data['channel']);
                                                            if (chat_data['channel_data'][channel_save]['msg_size']) {
                                                                cht_set_size(chat_data['channel_data'][channel_save]['msg_size']);
                                                            }
                                                            if (chat_data['channel_data'][channel_save]['msg_color_id'] != undefined && chat_data['channel_data'][channel_save]['msg_color_id'] >= 0) {
                                                                _top().chat.$('.channel[data-channel=' + channels_info[channel_save]['channel'] + '] span:first span:first').css('background-color', msg_color_ids[chat_data['channel_data'][channel_save]['msg_color_id']]['color']);
                                                                _top().chat.chat_text.$('.tcChs' + channel_save).removeClass().addClass('tcChs' + channel_save + ' cml_a' + chat_data['channel_data'][channel_save]['msg_color_id']);
                                                            }
                                                        });
                                                        return false;
                                                    };

                                                    function chatFrameSelect(type) {
                                                        var url = '';
                                                        var iframe = gebi('user_list');
                                                        if (!iframe) return;
                                                        switch (type) {
                                                            case 'area':
                                                                url = '/cht_iframe.php?mode=user';
                                                                break;
                                                            case 'party':
                                                                url = '/party_iframe.php?mode=members';
                                                                break;
                                                            case 'clan':
                                                                url = '/cht_iframe.php?mode=clan';
                                                                break;
                                                            case 'friends':
                                                                url = '/cht_iframe.php?mode=friends';
                                                                break;
                                                            case 'referals':
                                                                url = '/cht_iframe.php?mode=referals';
                                                                break;
                                                        }
                                                        if (url != '') iframe.src = url;
                                                        switch (type) {
                                                            default:
                                                                cht_btn_select('');
                                                                cht_btn_select2(type);
                                                                break;
                                                            case 'party':
                                                                cht_btn_select('party_members');
                                                                cht_btn_select2(type);
                                                                break;
                                                        }
                                                        switch (type) {
                                                            case 'area':
                                                                setTimeout('chatRefreshUsers(true)', 200);
                                                                break;
                                                        }
                                                    }

                                                    function chatActivateParty(settings, if_opened) {
                                                        var url = 'party_iframe.php?mode=members';
                                                        if (settings) {
                                                            url = 'party_iframe.php?mode=settings';
                                                        }
                                                        var iframe = gebi('user_list');
                                                        if (!iframe) return;
                                                        if (if_opened && iframe.src.indexOf(url) === -1) {
                                                            return;
                                                        }
                                                        $('#chat_user_list').find('> li').removeClass('selected').filter('.party').addClass('selected');
                                                        iframe.src = url;
                                                        if (settings) {
                                                            cht_btn_select('party_settings');
                                                        } else {
                                                            cht_btn_select('party_members');
                                                        }
                                                    }

                                                    function chatActivateNonparty() {
                                                        var iframe = gebi('user_list');
                                                        if (!iframe) return;
                                                        var url = 'cht_iframe.php?mode=user';
                                                        if (iframe.src.search(url) !== -1) {
                                                            chatRefreshUsers();
                                                        } else {
                                                            iframe.src = url;
                                                        }
                                                        cht_btn_select('area');
                                                    }

                                                    /*function chatActivateParty(if_opened) {
                                                        var url = 'party_iframe.php?mode=settings';
                                                        var iframe = gebi('user_list');
                                                        if (!iframe) return;
                                                        if (if_opened && iframe.src.indexOf(url) === -1) {
                                                            return;
                                                        }
                                                        $('#chat_user_list').find('> li').removeClass('selected').filter('.party').addClass('selected');
                                                        iframe.src = url;
                                                        cht_btn_select2('');
                                                        cht_btn_select('party_members');
                                                    }*/

                                                    function cht_btn_select(btn) {
                                                        $('.cht_buttons_state').removeClass('selected');
                                                        if (btn != '') $('#cht_' + btn + '_btn').addClass('selected');
                                                    }

                                                    function cht_btn_select2(btn) {
                                                        $('#chat_user_list li').removeClass('selected');
                                                        if (btn != '') $('#chat_user_list .' + btn).addClass('selected');
                                                    }

                                                    function chatActivateNonparty() {
                                                        var iframe = gebi('user_list');
                                                        if (!iframe) return;
                                                        var url = 'cht_iframe.php?mode=user';
                                                        if (iframe.src.search(url) !== -1) {
                                                            chatRefreshUsers();
                                                        } else {
                                                            iframe.src = url;
                                                        }
                                                        cht_btn_select('');
                                                    }
                                                </script>

                                            </td>

{{--                                            <td align="right" valign="bottom">--}}
{{--                                                <table cellpadding="0" cellspacing="0" width="50" height="21"--}}
{{--                                                       style="">--}}
{{--                                                    <tbody>--}}
{{--                                                    <tr>--}}
{{--                                                        <td width="50" height="26"><img--}}
{{--                                                                src="images/cht-resize-up.png"--}}
{{--                                                                width="25" height="26"--}}
{{--                                                                border="0"--}}
{{--                                                                title="Увеличить размер чата"--}}
{{--                                                                onclick="top.cht_resize(1); return false;"--}}
{{--                                                                style="cursor:pointer;"><img--}}
{{--                                                                src="images/cht-resize-down.png" width="25"--}}
{{--                                                                height="26"--}}
{{--                                                                border="0" title="Уменьшить размер чата"--}}
{{--                                                                onclick="top.cht_resize(-1); return false;"--}}
{{--                                                                style="cursor:pointer;"></td>--}}
{{--                                                    </tr>--}}
{{--                                                    <tr>--}}
{{--                                                        <td width="35" height="3" class="cht_ud cht_ud-cb"></td>--}}
{{--                                                    </tr>--}}
{{--                                                    </tbody>--}}
{{--                                                </table>--}}
{{--                                            </td>--}}

                                            <script>
                                                function close_instance() {
                                                    chatToggleChBtnStyle('inst_die_but', true);
                                                    $.ajax({
                                                        url: '/instance.php?action2=close_instance',
                                                        cache: false,
                                                        type: 'POST',
                                                    }).done(function (json) {
                                                        chatToggleChBtnStyle('inst_die_but', false);
                                                    });
                                                }

                                                function trigger_cron() {
                                                    chatToggleChBtnStyle('trigger_cron_but', true);
                                                    $.ajax({
                                                        url: '/1data.php',
                                                        cache: false,
                                                        type: 'POST',
                                                    }).done(function (json) {
                                                        chatToggleChBtnStyle('trigger_cron_but', false);
                                                    });
                                                }
                                            </script>
                                            <td>&nbsp;
                                            </td>
                                            <td width="60" id="fightexlog" style="display: none;">
                                                <img src="images/d.gif" width="1" height="1" alt="" border="0"><br>
                                                <img src="images/chat-exlog-l.gif" width="36" height="34" alt=""
                                                     border="0"><a href="#" onclick="exlog(); return false;"><img
                                                        src="images/chat-exlog-r.gif" width="24" height="34" alt=""
                                                        border="0"></a><br>
                                            </td>

                                            <td width="13"><img id="fightlog_but_IMGL" src="{{ asset('img/bg/chat/4_left_inact.png') }}"></td>
                                            <td id="fightlog_but_TD" width="50" class="tbl-main_chatchng-ina-c">
                                                <a id="fightlog_but_A" onclick="chatToggleFightlog();return false;" href="#" class="tbl-main_chatchng-link_inact">Лог боя</a>
                                            </td>
                                            <td width="20"><img id="fightlog_but_IMGR" src="{{ asset('img/bg/chat/4_right_inact.png') }}"></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr>
                                            <td valign="top">
                                                <iframe id="chat-frame" name="chat_text" height="100%" width="100%" frameborder="0" src="{{ route('chat.text') }}"></iframe>
                                            </td>
                                            <td width="330" id="fightlog" style="display: none;">
                                                <iframe name="chat_log" scrolling="yes" src="{{ route('chat.log') }}" frameborder="0" height="100%" width="100%"></iframe>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="335px" valign="top">
                        <table width="100%" border="0" height="100%" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="35">
                                <td colspan="3" class="tbl-main_chat-top-right">
                                    <ul id="chat_user_list">
                                        <li class="area selected" data-type="area" title="Список персонажей на локации"
                                            onclick="chatFrameSelect('area');">
                                            <span class="icon"><img src="{{ asset('img/bg/chat/cht-area-icon.png') }}" alt="Локация"></span>
                                            <span class="title">Локация</span>
                                        </li>
                                        <li id="chat_users_party" class="party hid" data-type="party"
                                            title="Список участников группы" onclick="chatFrameSelect('party');">
                                            <span class="icon"><img src="{{ asset('img/bg/chat/cht-party-icon.png') }}" alt="Группа"></span>
                                            <span class="title">Группа</span>
                                        </li>
                                    </ul>
                                </td>
                            </tr>
{{--                            <tr height="6">--}}
{{--                                <td class="tbl-main_separator-v" width="3"><img src="{{ asset('img/icon/d.gif') }}" width="3" height="1"></td>--}}
{{--                                <td align="right"><img src="{{ asset('img/bg/chat/chat_7.png') }}" width="267" height="6"></td>--}}
{{--                            </tr>--}}
                            <tr>
                                <td class="tbl-main_separator-v" width="3"><img src="{{ asset('img/icon/d.gif') }}" width="3" height="1"></td>
                                <td valign="top" height="100%">
                                    <iframe id="who-frame" height="100%" width="100%" scrolling="auto" frameborder="0" src="{{ route('who') }}"></iframe>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="37" valign="top">
                        <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="61">
                                <td><img src="{{ asset('img/bg/chat/tbl-main_chat-right-top.gif') }}" width="37" height="61"></td>
                            </tr>
                            <tr>
                                <td class="tbl-main_chat-right-center"><img src="{{ asset('img/icon/d.gif') }}" width="37" height="1"></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
{{--            <iframe id="chat-frame" height="100%" width="100%" frameborder="0" src="{{ route('chat.text') }}"></iframe>--}}
        </td>
{{--        <td width="335px"><iframe id="who-frame" height="100%" width="335px" frameborder="0" src="{{ route('who') }}"></iframe></td>--}}
    </tr>
    <tr style="height: 40px">
        <td colspan="2"><iframe height="45px" width="100%" frameborder="0" id="bottom-frame" src="{{ route('chat.action') }}"></iframe></td>
    </tr>
    </tbody>
</table>

</body>
</html>
