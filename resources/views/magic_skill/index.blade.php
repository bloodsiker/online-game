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
                                    <a id="center_2" href="{{ route('magic_skill', ['group' => 'magic_skill']) }}" title="Книга заклинаний" class="{{ $group === 'magic_skill' ? 'btn_2' : 'btn_1' }}">Книга заклинаний</a></td>
                                <td width="19"><img id="right_2" src="{{ asset($group === 'magic_skill' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
                            </tr>
                            </tbody>
                        </table>

                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">

                        <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Активные скилы</td>
                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                            </tr>
                            </tbody>
                        </table>

                        <br>

                        <table class="coll w100 p10h p2v brd2-all">
                            <tbody>
                            <tr>
                                <th width="30%">Название</th>
                                <th width="150px" style="white-space: nowrap; padding: 0 10px">Повреждение</th>
                                <th style="white-space: nowrap; padding: 0 10px">Отображать в бою</th>
                                <th>Описание</th>
                            </tr>
                            @foreach($activeSkills as $activeSkill)
                                <tr class="@if($loop->index % 2 == 0) bg_l @endif">
                                    <td class="brd2-top brd2-bt b">[{{ $activeSkill->mana_cost }}] {{ $activeSkill->name }}</td>
                                    <td class="brd2-top brd2-bt b" style="text-align: center">{{ $activeSkill->min_damage }} - {{ $activeSkill->max_damage }}</td>
                                    <td class="brd2-top brd2-bt b" style="text-align: center">
                                        <input type="checkbox" name="combo_in_fight[{{ $activeSkill->id }}]" value="{{ $activeSkill->id }}" @if($activeSkill->pivot->is_equipped) checked @endif  class="combo-in-fight" id="_chk_{{ $activeSkill->id }}">
                                    </td>
                                    <td class="brd2-top brd2-bt red">Наносит дополнительные 2 урона с ударом</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <br>
                        <div style="text-align: center;">
                            <span class="butt1 pointer"><span><input value="Сохранить" type="submit" onclick="saveCombos();"></span></span>
                        </div>

                        <br>

                        <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Пасивные скилы</td>
                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                            </tr>
                            </tbody>
                        </table>

                        <br>

                        <table class="coll w100 p10h p2v brd2-all">
                            <tbody>
                            <tr>
                                <th width="30%">Название</th>
                                <th width="">Описание</th>
                            </tr>
                            @foreach($passiveSkills as $passiveSkill)
                                <tr class="@if($loop->index % 2 == 0) bg_l @endif">
                                    <td class="brd2-top brd2-bt b">{{ $passiveSkill->name }}</td>
                                    <td class="brd2-top brd2-bt red">{{ $passiveSkill->description }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <br>

{{--                        <table border="0" cellspacing="0" cellpadding="0" style="margin: 0 auto;">--}}
{{--                            <tbody>--}}
{{--                            <tr height="22">--}}
{{--                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>--}}
{{--                                <td align="center" class="tbl-usi-hdr mbg">Дополнительные скилы</td>--}}
{{--                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>--}}
{{--                            </tr>--}}
{{--                            </tbody>--}}
{{--                        </table>--}}

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

</body>
</html>
