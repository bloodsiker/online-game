<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Ошибка</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <style>
            * {
                font-family: Tahoma, Geneva, sans-serif;
                font-size: 12px;
            }
            .regcolor, .regcolor * {
                color: #955c4a;
            }
            .popup-top-left {
                position: relative;
                background: url({{ asset('img/bg/modal/popup-top-left.png') }}) left top no-repeat;
            }
            .popup-top-right {
                background: url({{ asset('img/bg/modal/popup-top-right.png') }}) right top no-repeat;
            }
            .popup-top-center {
                margin: 0 36px 0 17px;
                background: url({{ asset('img/bg/modal/popup-top-center.png') }}) left top repeat-x;
            }
            .popup_global_title {
                height: 24px;
                padding-top: 10px;
                color: #f5f4bf;
                font-weight: 700;
                text-align: center;
            }
            .popup_global_close_btn {
                position: absolute;
                right: -2px;
                top: -2px;
                width: 33px;
                height: 35px;
                background: url({{ asset('img/bg/modal/popup-close.png') }}) right top no-repeat;
                cursor: pointer;
            }
            .popup-left-center {
                position: relative;
                background: url({{ asset('img/bg/modal/popup-left-center.png') }}) left top repeat-y;
            }
            .popup-right-center {
                background: url({{ asset('img/bg/modal/popup-right-center.png') }}) right top repeat-y;
            }
            .popup_global_content {
                overflow: hidden;
                margin: 0 18px;
                background: url({{ asset('img/bg/modal/popup-main-bg.png') }}) center center;
            }
            .popup-left-bottom {
                background: url({{ asset('img/bg/modal/popup-left-bottom.png') }}) left bottom no-repeat;
            }
            .popup-right-bottom {
                background: url({{ asset('img/bg/modal/popup-right-bottom.png') }}) right bottom no-repeat;
            }
            .popup-bottom-center {
                height: 17px;
                margin: 0 18px;
                background: url({{ asset('img/bg/modal/popup-bottom-center.png') }}) center bottom repeat-x;
            }
            b.butt1, b.butt1.disabled {
                height: 38px;
                font-size: 26px;
                cursor: pointer;
                background: url({{ asset('img/bg/btn/tbl-btn2_c-left.png') }}) left 4px no-repeat;
                display: inline-block;
            }
            b.butt1 b, b.butt1.disabled b {
                height: 38px;
                font-size: 26px;
                cursor: pointer;
                background: url({{ asset('img/bg/btn/tbl-btn2_c-right.png') }}) right 4px no-repeat;
                display: inline-block;
            }
            b.butt1 input, b.butt1 button.butt1, b.butt1.disabled input, b.butt1.disabled button {
                height: 38px;
                border: 0;
                color: #f8dea4 !important;
                cursor: pointer;
                font-family: Tahoma;
                font-size: 11px !important;
                font-weight: 700;
                text-decoration: none;
                margin: 0 33px;
                background: transparent url({{ asset('img/bg/btn/tbl-btn2_center.png') }}) center top repeat-x;
                padding-bottom: 3px;
                outline: none;
            }
        </style>
    </head>
    <body class="regcolor" style="background: transparent;">
    <div class="popup_global_container">
        <div class="popup-top-left">
            <div class="popup-top-right">
                <div class="popup-top-center">
                    <div class="popup_global_title" id="action_title_amount">Сообщение</div>
                </div>
            </div>
            <div class="popup_global_close_btn" onclick="window.parent.hideErrorIframe();"></div>
        </div>
        <div class="popup-left-center">
            <div class="popup-right-center">
                <div class="popup_global_content" style="padding: 20px;">
                    <div style="text-align: center;">
                        <b>{!! $message !!}</b><br>
                    </div>
                    <div style="margin-top: 25px; text-align: center;">
                        <b class="butt1 pointer"><b><input value="Закрыть" type="button" onclick="window.parent.hideErrorIframe();" class="redd" style="width: 100px;"></b></b>
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
    </body>
</html>
