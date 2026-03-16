<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Ошибка</title>
        <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <style>
            * {
                font-family: Tahoma, Geneva, sans-serif;
                font-size: 12px;
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
