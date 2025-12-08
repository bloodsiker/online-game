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
            color: #000;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        .main-table {
            width: 100%;
            height: 100%;
        }
        .center {
            text-align: center;
        }
        .location-name {

        }
        .location-description {
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .battle-description {
            margin-top: 15px;
        }
        .side-move {
            margin-top: 30px;
        }
        .mt-10 {
            margin-top: 10px;
        }
        .color-red {
            color: red;
        }
        .bg {
            background-color: #000;
            background-image: url({{ asset('img/bg/bg.gif') }});
            background-attachment: fixed;
            background-position: 0 5px;
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
    </style>
</head>
<body>

<table class="main-table" width="100%" height="100%" style="height: 100%" cellpadding="10" cellspacing="0" border="0">
    <tbody>
    <tr style="vertical-align: top">
        <td>
            <iframe width="100%" name="location" height="100%" frameborder="0" id="location-frame" src="{{ route('location') }}"></iframe>
        </td>

{{--        <td width="300px" height="300px">--}}
{{--            <iframe width="100%" name="map" height="100%" frameborder="0" id="map-frame" src="{{ route('on_map') }}"></iframe>--}}
{{--        </td>--}}
    </tr>
    </tbody>
</table>

<script>
    {{--document.addEventListener('keydown', function(event) {--}}
    {{--    switch (event.key.toLowerCase()) {--}}
    {{--        case 'arrowup':--}}
    {{--            document.getElementById('move-north').click();--}}
    {{--            break;--}}
    {{--        case 'arrowdown':--}}
    {{--            document.getElementById('move-south').click();--}}
    {{--            break;--}}
    {{--        case 'arrowleft':--}}
    {{--            document.getElementById('move-west').click();--}}
    {{--            break;--}}
    {{--        case 'arrowright':--}}
    {{--            document.getElementById('move-east').click();--}}
    {{--            break;--}}
    {{--        case 'f':--}}
    {{--            document.getElementById('take-item').click();--}}
    {{--            break;--}}
    {{--        case 'i':--}}
    {{--            sendDataToGame('{{ route('backpack') }}');--}}
    {{--            break;--}}
    {{--        case 'c':--}}
    {{--            sendDataToGame('{{ route('character') }}');--}}
    {{--            break;--}}
    {{--        case ' ':--}}
    {{--            document.getElementById('attack').click();--}}
    {{--            break;--}}
    {{--        default:--}}
    {{--            return;--}}
    {{--    }--}}
    {{--    event.preventDefault();--}}
    {{--});--}}

    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }
</script>

</body>
</html>
