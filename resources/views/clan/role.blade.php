<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клан</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * {
            font-family: Tahoma;
            font-size: 11px;
        }
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #000;
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

    </style>
</head>
<body class="regblk">

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
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
        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="100" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('clan.member') }}" title="Состав клана" class="btn_1">Состав клана</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

        <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
        <td width="60" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('clan.role') }}" title="Звания" class="btn_2">Звания</a></td>
        <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="70" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('clan.information') }}" title="Информация" class="btn_1">Информация</a></td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="70" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="" title="Управление" class="btn_1">Управление</a></td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>

        <td></td>
    </tr>
    </tbody>
</table>

<table class="coll" width="100%" height="100%" border="0">
    <tbody>
    <tr>
        <td valign="top" width="100%">
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
                                <td align="center" class="tbl-usi_label-center">Звания клана</td>
                                <td width="27">
                                    <img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top">

                        <form method="post" action="{{ route('clan.role.save') }}">
                            @csrf
                            <table class="coll w100 p10h p4v brd2 clan-mng-grade-form"
                                   style="margin-top:12px;margin-bottom:12px">
                                <tbody>
                                <tr>
                                    <td rowspan="2" class="b" align="center">Звание</td>
                                    <td colspan="{{ count($permissions) }}" class="b" align="center">
                                        Полномочия
                                    </td>
                                    <td rowspan="2" class="b" align="center" width="100">Действия</td>
                                </tr>
                                <tr>
                                    @foreach($permissions as $perm)
                                        <td align="center" title="{{ $perm->label() }}"
                                            class="b grade_general" style="display: table-cell;" width="20%"
                                            nowrap="">{!! $perm->columnTitle() !!}
                                        </td>
                                    @endforeach
                                </tr>

                                @foreach($roles as $i => $role)
                                    <tr class="{{ $i % 2 === 0 ? 'bg_l' : '' }}">
                                        <td width="175" nowrap="" height="31">
                                            @if($role->is_leader || !$canChangePerms)
                                                <input type="text" value="{{ $role->name }}" size="16"
                                                       maxlength="16" style="width:170px" disabled>
                                            @else
                                                <input type="text" name="form[grades][{{ $role->id }}][title]"
                                                       value="{{ $role->name }}" size="16" maxlength="16"
                                                       style="width:170px">
                                            @endif
                                        </td>
                                        @foreach($permissions as $perm)
                                            <td nowrap="" align="center" class="grade_general"
                                                style="display: table-cell;">
                                                @if($role->is_leader || !$canChangePerms)
                                                    @if($role->hasPermission($perm))
                                                        <img src="{{ asset('img/icon/check_yes.gif') }}" style="width: 15px; height: 15px">
                                                    @else
                                                        &mdash;
                                                    @endif
                                                @else
                                                    <label for="chk-{{ $role->id }}-{{ $perm->value }}">
                                                        <input type="checkbox"
                                                               id="chk-{{ $role->id }}-{{ $perm->value }}"
                                                               name="form[grades][{{ $role->id }}][flags][{{ $perm->value }}]"
                                                               value="1"
                                                               class="input-custom grade_checkbox"
                                                               {{ $role->hasPermission($perm) ? 'checked' : '' }}>
                                                        <span class="custom-checkbox"></span>
                                                    </label>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td nowrap="" width="60" align="center">
                                            @if(!$role->is_default)
                                                <b class="butt2 pointer"><b>
                                                    <input value="Удалить" type="button" style="width:60px"
                                                           onclick="return top.systemConfirm('Вы действительно хотите удалить роль?','Действие',false,function(){ submitDeleteRole({{ $role->id }}) });">
                                                </b></b>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                </tbody>
                            </table>

                            @if($canChangePerms)
                                <div align="center">
                                    <span class="butt1 pointer">
                                        <span>
                                            <input value="Сохранить изменения" type="submit"
                                                   class="grnn"
                                                   style="width:160px;">
                                        </span>
                                    </span>
                                </div>
                            @endif
                        </form>

                        <form id="form_delete_role" method="post" action="" style="display:none">
                            @csrf
                            @method('DELETE')
                        </form>

                        @if($canChangePerms)
                            <form method="post" action="{{ route('clan.role.add') }}">
                                @csrf
                                <table cellspacing="0" cellpadding="5" border="0">
                                    <tbody><tr>
                                        <td valign="middle">Создать звание</td>
                                        <td valign="middle" width="100"><input type="text" name="name" value="{{ old('name') }}" class="w100 dbgl2 brd b small" maxlength="16"></td>
                                        <td valign="middle"><b class="butt2 pointer"><b><input value="Добавить" type="submit" style="width:130px"></b></b></td>
                                    </tr></tbody>
                                </table>
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
    function submitDeleteRole(roleId) {
        var form = document.getElementById('form_delete_role');
        form.action = '{{ url('clan/role') }}/' + roleId;
        form.submit();
    }

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

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
