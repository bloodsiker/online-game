<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Книга заклинаний</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html { height: 100%; }
        body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 11px; }

        a { color: #000; }
        a:hover { color: #353434; }

        .tbl-shp-sml { background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sides { background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat; font-size: 0; }
        .tbl-usi_bg { background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }

        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }

        /* Section header */
        .section-header {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 10px 10px 6px;
            border-bottom: 1px solid #db9f73;
            padding-bottom: 4px;
        }
        .section-header span {
            font-weight: bold;
            font-size: 12px;
            color: #5a2a0a;
            letter-spacing: 0.5px;
        }
        .section-count {
            background: #db9f73;
            color: #fff;
            border-radius: 8px;
            padding: 0 5px;
            font-size: 10px;
            font-weight: bold;
            line-height: 14px;
        }

        /* Active skill cards */
        .skill-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            padding: 0 10px 10px;
        }
        .skill-card {
            width: 185px;
            border: 1px solid #db9f73;
            border-radius: 3px;
            background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }});
            background-repeat: repeat;
            position: relative;
            overflow: hidden;
        }
        .skill-card-header {
            background: linear-gradient(to bottom, #e8c49a, #d4a87a);
            border-bottom: 1px solid #db9f73;
            padding: 4px 6px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .skill-card-name {
            font-weight: bold;
            font-size: 11px;
            color: #3a1500;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .mana-badge {
            background: #4a6fa5;
            color: #cce0ff;
            font-size: 10px;
            font-weight: bold;
            padding: 1px 5px;
            border-radius: 8px;
            white-space: nowrap;
            margin-left: 4px;
            flex-shrink: 0;
        }
        .skill-card-body {
            padding: 5px 6px;
        }
        .skill-dmg {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 4px;
        }
        .skill-dmg-label {
            color: #888;
        }
        .skill-dmg-value {
            color: #8D2616;
            font-weight: bold;
        }
        .skill-desc {
            color: #555;
            line-height: 1.4;
            margin-bottom: 5px;
            min-height: 28px;
        }
        .skill-equip-row {
            display: flex;
            align-items: center;
            gap: 5px;
            border-top: 1px solid #e8c49a;
            padding-top: 4px;
        }
        .equip-label {
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Passive skill pills */
        .passive-list {
            padding: 0 10px 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .passive-card {
            border: 1px solid #db9f73;
            border-radius: 3px;
            background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }});
            background-repeat: repeat;
            padding: 5px 8px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .passive-icon {
            width: 8px;
            height: 8px;
            background: #db9f73;
            border-radius: 50%;
            margin-top: 3px;
            flex-shrink: 0;
        }
        .passive-name {
            font-weight: bold;
            color: #3a1500;
            white-space: nowrap;
            margin-right: 4px;
        }
        .passive-desc {
            color: #666;
        }
        .passive-effects {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 3px;
        }
        .effect-badge {
            background: #2a6e1a;
            color: #d4f5c8;
            font-size: 10px;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 8px;
            white-space: nowrap;
        }

        .empty-state {
            padding: 16px 10px;
            color: #888;
            font-style: italic;
            text-align: center;
        }

        .save-row {
            padding: 6px 10px 10px;
            text-align: center;
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
                                $btnLeft1   = 'img/bg/btn/btn-left1.gif';
                                $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
                                $btnRight1  = 'img/bg/btn/btn-right1.gif';
                                $btnLeft2   = 'img/bg/btn/btn-left2.gif';
                                $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
                                $btnRight2  = 'img/bg/btn/btn-right2.gif';
                            @endphp
                            <tr height="21">
                                <td width="19"><img src="{{ asset($group === 'character' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td align="center" style="background: url({{ asset($group === 'character' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a href="{{ route('character', ['group' => 'character']) }}" class="{{ $group === 'character' ? 'btn_2' : 'btn_1' }}">Персонаж</a>
                                </td>
                                <td width="19"><img src="{{ asset($group === 'character' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img src="{{ asset($group === 'magic_skill' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td align="center" style="background: url({{ asset($group === 'magic_skill' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a href="{{ route('magic_skill', ['group' => 'magic_skill']) }}" class="{{ $group === 'magic_skill' ? 'btn_2' : 'btn_1' }}">Книга заклинаний</a>
                                </td>
                                <td width="19"><img src="{{ asset($group === 'magic_skill' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>

                                <td width="19"><img src="{{ asset($group === 'slots' ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                                <td align="center" style="background: url({{ asset($group === 'slots' ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 3px;">
                                    <a href="{{ route('slots', ['group' => 'slots']) }}" class="{{ $group === 'slots' ? 'btn_2' : 'btn_1' }}">Слоты</a>
                                </td>
                                <td width="19"><img src="{{ asset($group === 'slots' ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0;">

                        {{-- Active skills --}}
                        <div class="section-header">
                            <span>Активные заклинания</span>
                            <span class="section-count">{{ $activeSkills->count() }}</span>
                        </div>

                        @if($activeSkills->isEmpty())
                            <div class="empty-state">Нет активных заклинаний</div>
                        @else
                            <div class="skill-grid">
                                @foreach($activeSkills as $skill)
                                    <div class="skill-card">
                                        <div class="skill-card-header">
                                            <span class="skill-card-name" title="{{ $skill->name }}">{{ $skill->name }}</span>
                                            @if($skill->mana_cost > 0)
                                                <span class="mana-badge">{{ $skill->mana_cost }} MP</span>
                                            @endif
                                        </div>
                                        <div class="skill-card-body">
                                            @if($skill->min_damage > 0 || $skill->max_damage > 0)
                                                <div class="skill-dmg">
                                                    <span class="skill-dmg-label">Урон:</span>
                                                    <span class="skill-dmg-value">{{ $skill->min_damage }} – {{ $skill->max_damage }}</span>
                                                </div>
                                            @endif
                                            <div class="skill-desc">{{ $skill->description ?: '—' }}</div>
                                            <div class="skill-equip-row">
                                                <label class="equip-label" for="chk_skill_{{ $skill->id }}">
                                                    <input
                                                        type="checkbox"
                                                        class="input-custom combo-in-fight"
                                                        id="chk_skill_{{ $skill->id }}"
                                                        value="{{ $skill->id }}"
                                                        @if($skill->pivot->is_equipped) checked @endif
                                                    >
                                                    <span class="custom-checkbox"></span>
                                                    Показывать в бою
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="save-row">
                                <span class="butt1 pointer">
                                    <span><input value="Сохранить" type="button" onclick="saveCombos();"></span>
                                </span>
                            </div>
                        @endif

                        {{-- Passive skills --}}
                        <div class="section-header" style="margin-top: 4px;">
                            <span>Пассивные навыки</span>
                            <span class="section-count">{{ $passiveSkills->count() }}</span>
                        </div>

                        @if($passiveSkills->isEmpty())
                            <div class="empty-state">Нет пассивных навыков</div>
                        @else
                            @php
                                $effectLabels = [
                                    'str'     => 'Сила',
                                    'int'     => 'Интеллект',
                                    'agil'    => 'Ловкость',
                                    'hp_max'  => 'Макс. HP',
                                    'mp_max'  => 'Макс. MP',
                                    'attack'  => 'Атака',
                                    'defense' => 'Защита',
                                ];
                            @endphp
                            <div class="passive-list">
                                @foreach($passiveSkills as $skill)
                                    <div class="passive-card">
                                        <div class="passive-icon"></div>
                                        <div>
                                            <div>
                                                <span class="passive-name">{{ $skill->name }}</span>
                                                <span class="passive-desc">{{ $skill->description ?: '—' }}</span>
                                            </div>
                                            @if(!empty($skill->effects))
                                                <div class="passive-effects">
                                                    @foreach($skill->effects as $effect)
                                                        <span class="effect-badge">
                                                             {{ $effectLabels[$effect['type']] ?? $effect['type'] }}
                                                            +{{ $effect['value'] }}{{ !empty($effect['is_percent']) ? '%' : '' }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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
    document.addEventListener('keydown', function(event) {
        switch (event.key.toLowerCase()) {
            case 'i': window.parent.sendDataToGame('{{ route('backpack') }}'); break;
            case 'c': window.parent.sendDataToGame('{{ route('character') }}'); break;
            case ' ': window.parent.sendDataToGame('{{ route('location') }}'); break;
            default: return;
        }
        event.preventDefault();
    });

    function saveCombos() {
        const params = { skills: [] };
        document.querySelectorAll('.combo-in-fight').forEach(el => {
            if (el.checked) params.skills.push(el.value);
        });

        fetch('{{ route('magic_skill.update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(params)
        })
        .then(r => r.json())
        .then(data => window.parent.showErrorIframe(data.message || 'Сохранено'))
        .catch(() => window.parent.showErrorIframe('Ошибка при сохранении'));
    }

    @if(session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
