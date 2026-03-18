<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клан</title>
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
            font-size: 11px;
        }
        a, a:link, a:visited, a:active {
            text-decoration: none;
        }
        .p10h, .p10h td {
            padding-left: 10px;
            padding-right: 10px;
        }
        .p4v, .p4v td {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        table.coll {
            border-collapse: collapse;
            border-spacing: 0;
        }
        .brd, .brd td {
            border: 1px solid #C49485;
        }
        .brd2-all {
            border: 1px solid #DB9F73;
        }
        .bg_l {
            background-image: url(/img/bg/info/bg_l.gif);
        }
        .bg_l2 {
            background-image: url(/img/bg/info/bg_l2.gif);
            cursor: pointer;
        }
        .brd2, .brd2 td {
            border: 1px solid #DB9F73;
        }
        .dbgl2 {
            background-color: #FFFBD6;
        }
        .grnn {
            color: #114d01 !important;
        }
        textarea.info-textarea {
            width: 100%;
            box-sizing: border-box;
            resize: vertical;
        }

    </style>
</head>
<body class="regblk">

@include('clan.partials.tabs', ['activeTab' => 'clan.information'])

<table class="coll" width="100%" height="100%" border="0" style="margin-top: 20px;">
    <tbody>
    <tr>
        <td valign="top" width="100%">

            {{-- Описание клана --}}
            <table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27">
                                    <img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22">
                                </td>
                                <td align="center" class="tbl-usi_label-center">Описание клана</td>
                                <td width="27"
                                ><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 8px 10px">

                        @if($canChangeNews)
                        <form method="post" action="{{ route('clan.information.save-description') }}">
                            @csrf
                        @endif

                        <textarea name="description" rows="15"
                                  class="info-textarea dbgl2 brd b small lscroll"
                                  @if(!$canChangeNews) readonly @endif
                        >{{ old('description', $clan->description) }}</textarea>

                        @if($canChangeNews)
                            <div align="center" style="padding: 8px 0 4px;">
                                <b class="butt1 pointer"><b>
                                    <input value="Сохранить изменения" type="submit" class="grnn" style="width:160px;"
                                           onclick="if(document._submit_desc)return false;document._submit_desc=true;">
                                </b></b>
                            </div>
                        </form>
                        @endif

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

            {{-- Новости --}}
            <table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%" style="margin-top: 30px;">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27">
                                    <img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22">
                                </td>
                                <td align="center" class="tbl-usi_label-center">Новости</td>
                                <td width="27"
                                ><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 8px 10px">

                        @if($canChangeNews)
                        <form method="post" action="{{ route('clan.information.save-news') }}">
                            @csrf
                        @endif

                        Новость 1:<br>
                        <textarea name="news_1" rows="6" class="info-textarea dbgl2 brd b small lscroll"
                                  @if(!$canChangeNews) readonly @endif
                        >{{ old('news_1', $clan->news_1) }}</textarea>

                        Новость 2:<br>
                        <textarea name="news_2" rows="6" class="info-textarea dbgl2 brd b small lscroll"
                                  @if(!$canChangeNews) readonly @endif
                        >{{ old('news_2', $clan->news_2) }}</textarea>

                        Новость 3:<br>
                        <textarea name="news_3" rows="6"
                                  class="info-textarea dbgl2 brd b small lscroll"
                                  @if(!$canChangeNews) readonly @endif
                        >{{ old('news_3', $clan->news_3) }}</textarea>

                        @if($canChangeNews)
                            <div align="center" style="padding: 8px 0 4px;">
                                <b class="butt1 pointer"><b>
                                    <input value="Сохранить изменения" type="submit" class="grnn" style="width:160px;"
                                           onclick="if(document._submit_news)return false;document._submit_news=true;">
                                </b></b>
                            </div>
                        </form>
                        @endif

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
    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
