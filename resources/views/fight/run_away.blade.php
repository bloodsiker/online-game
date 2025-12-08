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
            padding: 0;
            color: #000000;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        li {
            list-style-type: none;
            padding-left: 20px;
            background-image: url('{{ asset('img/icon/users-arrow.gif') }}');
{{--            background-image: url('{{ asset('img/icon/li1.png') }}');--}}
            background-repeat: no-repeat;
            background-position: left center;
            background-size: 14px 12px;
        }
        .mb-5 {
            margin-bottom: 5px;
        }
        .color-red {
            color: red;
        }
        .t1 {
            background: url({{ asset('img/bg/bg_l.gif') }});
        }
        .t0 {
            /*background-color: #ffffff;*/
            background: url({{ asset('img/bg/bg_l.gif') }});
            {{--background-image: url({{ asset('img/bg/tbl-main_chat-top.gif') }});--}}
            {{--background-repeat: repeat-x;--}}
            {{--height: 35px;--}}
        }
        .l0 {
            /*background-color: #a7a7a7;*/
            {{--background: url({{ asset('img/bg/bg_l.gif') }}) left top;--}}
            background: url({{ asset('img/bg/common-bg.png') }});
        }
        .b {
            /*background-color: #db9f73;*/
        }
        .tbgr {
{{--            background: url({{ asset('img/bg/bg_l.gif') }});--}}
        }
        .main-table {
            width: 100%;
            height: 100%;
        }



        .tbl-sts_top {
            background-image: url({{ asset('img/bg/tbl-sts_top.gif') }});
            background-repeat: repeat-x;
            background-position: bottom;
            height: 19px;
        }
        .tbl-sts-bb {
            background: url({{ asset('img/bg/tbl-sts.png') }}) left top repeat-x;
        }
        .tbl-sts b {
            background: url({{ asset('img/bg/tbl-sts.png') }}) no-repeat;
            display: block;
            height: 19px;
            overflow: hidden;
            width: 19px;
        }
        .tbl-sts-lt b {
            background-position: 0 -50px;
        }
        .tbl-sts-rt b {
            background-position: 0 -100px;
        }
        .tbl-sts-lb b {
            background-position: 0 -170px;
        }
        .tbl-sts-rb b {
            background-position: 0 -219px;
        }
        .tbl-sts-ltb b {
            background-position: 0 -69px;
            height: 20px;
        }
        .tbl-sts-lbt b {
            background-position: 0 -150px;
            height: 20px;
        }
        .tbl-sts-rtb b {
            background-position: 0 -119px;
            height: 20px;
        }
        .tbl-sts-rbt b {
            background-position: 0 -200px;
            height: 20px;
        }
        .tbl-sts_left {
            background-image: url({{ asset('img/bg/tbl-sts_left.gif') }});
            background-repeat: repeat-y;
            width: 19px;
            background-position: right;
        }
        .tbl-sts_right {
            background-image: url({{ asset('img/bg/tbl-sts_right.gif') }});
            background-repeat: repeat-y;
            width: 19px;
        }
        .bgg {
            background-image: url({{ asset('img/bg/bgg.gif') }});
        }
        .achieve_bg {
            background: url({{ asset('img/bg/bg_l.gif') }}) left top;
        }
        .achieve_bg_lt {
            width: 10px;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_lt.jpg') }}) no-repeat left top;
        }
        .achieve_bg_tr {
            width: 100%;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_tr.jpg') }}) repeat-x left top;
        }
        .achieve_bg_rt {
            width: 10px;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_rt.jpg') }}) no-repeat left top;
        }
        .achieve_bg_lb {
            width: 10px;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_lb.jpg') }}) no-repeat left top;
        }
        .achieve_bg_br {
            width: 100%;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_br.jpg') }}) repeat-x left top;
        }
        .achieve_bg_rb {
            width: 10px;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_rb.jpg') }}) no-repeat left top;
        }
        .achieve_bg_lr {
            background: url({{ asset('img/bg/achieve_bg_lr.jpg') }}) repeat-y left top;
        }
        .achieve_bg_rr {
            background: url({{ asset('img/bg/achieve_bg_rr.jpg') }}) repeat-y left top;
        }

        .brd2-all {
            /*border: 1px solid #db9f73;*/
        }



        .common-inset-2-tl, .common-inset-2-tr, .common-inset-2-bl, .common-inset-2-br {
            font-size: 0;
            letter-spacing: -1em;
            word-spacing: -1em;
            background: url({{ asset('img/bg/common-corners.png') }}) no-repeat;
        }
        .common-inset-2-t, .common-inset-2-b {
            font-size: 0;
            letter-spacing: -1em;
            word-spacing: -1em;
            background: url({{ asset('img/bg/common-tb.png') }}) repeat-x;
        }
        .common-inset-2-tl, .common-inset-2-tr, .common-inset-2-bl, .common-inset-2-br {
            font-size: 0;
            letter-spacing: -1em;
            word-spacing: -1em;
            background: url({{ asset('img/bg/common-corners.png') }}) no-repeat;
        }
        .common-inset-2-l, .common-inset-2-r {
            font-size: 0;
            letter-spacing: -1em;
            word-spacing: -1em;
            background: url({{ asset('img/bg/common-lr.png') }}) repeat-y;
        }
        .common-inset-2-tr {
            background-position: 100% 0;
        }
        .common-inset-2-t {
            background-position: 0 0;
        }
        .common-inset-2-tr {
            background-position: 100% 0;
        }
        .common-inset-2-bl {
            background-position: 0 100%;
        }
        .common-inset-2-br {
            background-position: 100% 100%;
        }

    </style>
</head>
<body>

<table class="main-table" cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            <p><u><b>Раунд N 1</b></u> - <a href="/info/?mid=491625111" target="_blank">Пещерная тварь</a> 1 (50/50)</p>

            <p>Вы бежали на запад...</p>

            <p><a href="{{ route('location') }}" id="finish-fight">Сражение завершено... Далее</a> »</p>

        </td>
    </tr>
    </tbody>
</table>

<script>
    {{--document.addEventListener('keydown', function(event) {--}}
    {{--    switch (event.key.toLowerCase()) {--}}
    {{--        case 'i':--}}
    {{--            window.parent.sendDataToGame('{{ route('backpack') }}');--}}
    {{--            break;--}}
    {{--        case 'c':--}}
    {{--            window.parent.sendDataToGame('{{ route('character') }}');--}}
    {{--            break;--}}
    {{--        case ' ':--}}
    {{--            var finishFightButton = document.getElementById('finish-fight');--}}
    {{--            if (finishFightButton) {--}}
    {{--                finishFightButton.click();--}}
    {{--            } else {--}}
    {{--                window.parent.sendDataToGame('{{ route('location') }}');--}}
    {{--            }--}}
    {{--            break;--}}
    {{--        default:--}}
    {{--            return;--}}
    {{--    }--}}
    {{--    event.preventDefault();--}}
    {{--});--}}
</script>

</body>
</html>
