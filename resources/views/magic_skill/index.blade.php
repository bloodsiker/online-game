<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Книга заклинаний</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/lib/Sortable.min.js') }}"></script>
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
            background-image: url({{ asset('img/bg/common-bg.png') }});
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
            color: #ffffff;
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
            background-image: url({{ asset('img/bg/common-bg.png') }});
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

        .skill-use-row {
            border-top: 1px solid #e8c49a;
            padding-top: 5px;
            margin-top: 4px;
        }
        .skill-use-row select {
            width: 100%;
            font-size: 10px;
            border: 1px solid #db9f73;
            background: #fdf5e8;
            padding: 1px 3px;
            margin-bottom: 3px;
            border-radius: 2px;
        }
        .btn-use {
            display: inline-block;
            background: linear-gradient(to bottom, #6aaa3a, #4e8a25);
            color: #fff !important;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 3px;
            cursor: pointer;
            border: 1px solid #3a6a18;
            text-decoration: none;
        }
        .btn-use:hover { background: linear-gradient(to bottom, #7ac040, #5a9a2e); }
        .skill-type-badge {
            font-size: 9px;
            padding: 1px 4px;
            border-radius: 6px;
            margin-left: 3px;
            font-weight: bold;
        }
        .badge-buff { background: #2a6e1a; color: #d4f5c8; }
        .badge-heal { background: #1a5e6e; color: #c8f0f5; }
        .badge-attack { background: #6e1a1a; color: #f5c8c8; }

        .drag-handle {
            cursor: grab;
            color: #b08060;
            font-size: 13px;
            padding: 0 3px;
            flex-shrink: 0;
            line-height: 1;
            user-select: none;
        }
        .drag-handle:active { cursor: grabbing; }
        .skill-card.sortable-ghost { opacity: 0.4; }
        .skill-card.sortable-chosen { box-shadow: 0 2px 8px rgba(0,0,0,0.25); }
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
                        @include('character.partials.tabs')
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0;">

                        @php
                            $combatSkills = $activeSkills->filter(fn($s) => !$s->isBuffSkill());
                            $buffSkills   = $activeSkills->filter(fn($s) => $s->isBuffSkill());
                        @endphp

                        {{-- Combat skills --}}
                        <div class="section-header">
                            <span>Боевые заклинания</span>
                            <span class="section-count">{{ $combatSkills->count() }}</span>
                        </div>

                        @if($combatSkills->isEmpty())
                            <div class="empty-state">Нет боевых заклинаний</div>
                        @else
                            <div class="skill-grid" id="combat-grid">
                                @foreach($combatSkills as $skill)
                                    <div class="skill-card" data-id="{{ $skill->id }}">
                                        <div class="skill-card-header">
                                            <span class="drag-handle" title="Перетащить">⠿</span>
                                            <span class="skill-card-name" title="{{ $skill->name }}">{{ $skill->name }}</span>
                                            @if($skill->mana_cost > 0)
                                                <span class="mana-badge">{{ $skill->mana_cost }} MP</span>
                                            @endif
                                            <span class="skill-type-badge badge-attack">Атака</span>
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

                        {{-- Buff / Heal skills --}}
                        <div class="section-header" style="margin-top: 4px;">
                            <span>Баффы и исцеление</span>
                            <span class="section-count">{{ $buffSkills->count() }}</span>
                        </div>

                        @if($buffSkills->isEmpty())
                            <div class="empty-state">Нет баффов</div>
                        @else
                            <div class="skill-grid" id="buff-grid">
                                @foreach($buffSkills as $skill)
                                    <div class="skill-card" data-id="{{ $skill->id }}">
                                        <div class="skill-card-header">
                                            <span class="drag-handle" title="Перетащить">⠿</span>
                                            <span class="skill-card-name" title="{{ $skill->name }}">{{ $skill->name }}</span>
                                            @if($skill->mana_cost > 0)
                                                <span class="mana-badge">{{ $skill->mana_cost }} MP</span>
                                            @endif
                                            <span class="skill-type-badge {{ $skill->type === 'heal' ? 'badge-heal' : 'badge-buff' }}">
                                                {{ $skill->type === 'heal' ? 'Лечение' : 'Бафф' }}
                                            </span>
                                        </div>
                                        <div class="skill-card-body">
                                            @if($skill->base_healing > 0)
                                                <div class="skill-dmg">
                                                    <span class="skill-dmg-label">Лечение:</span>
                                                    <span class="skill-dmg-value" style="color:#1a6e1a">+{{ $skill->base_healing }} HP</span>
                                                </div>
                                            @endif
                                            <div class="skill-desc">{{ $skill->description ?: '—' }}</div>
                                            <div class="skill-use-row">
                                                @if($allyTargets->isNotEmpty() && $skill->target_type === 'all')
                                                    <select id="target_{{ $skill->id }}">
                                                        <option value="">— Себе —</option>
                                                        @foreach($allyTargets as $ally)
                                                            <option value="{{ $ally->id }}">{{ $ally->user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                                <a class="btn-use" onclick="useSkill({{ $skill->id }}, '{{ route('magic_skill.use', $skill->id) }}')">Применить</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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

    function useSkill(skillId, url) {
        const targetEl = document.getElementById('target_' + skillId);
        const targetPlayerId = targetEl ? targetEl.value : '';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ target_player_id: targetPlayerId || null })
        })
        .then(r => r.json())
        .then(data => {
            window.parent.showErrorIframe(data.message || 'Применено');
        })
        .catch(() => window.parent.showErrorIframe('Ошибка при применении'));
    }

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

    function saveSkillOrder(gridId) {
        const grid = document.getElementById(gridId);
        if (!grid) return;
        const ids = Array.from(grid.querySelectorAll('.skill-card')).map(el => el.dataset.id);

        fetch('{{ route('magic_skill.order') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ ids })
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['combat-grid', 'buff-grid'].forEach(function (gridId) {
            const el = document.getElementById(gridId);
            if (!el) return;
            Sortable.create(el, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function () { saveSkillOrder(gridId); }
            });
        });
    });
</script>

</body>
</html>
