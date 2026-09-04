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
        .equip-label.is-saving { cursor: wait; opacity: .65; }

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

        .skill-effect-row {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            margin-bottom: 2px;
        }
        .skill-effect-name {
            color: #3a6e1a;
            font-weight: bold;
            flex: 1;
        }
        .skill-effect-duration {
            color: #888;
            white-space: nowrap;
        }
        .skill-effect-chance {
            background: #e8c49a;
            color: #5a2a0a;
            border-radius: 6px;
            padding: 0 4px;
            white-space: nowrap;
            font-weight: bold;
        }
        .skill-cooldown-info {
            color: #888;
            font-size: 10px;
            margin-bottom: 4px;
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
        .btn-use.on-cooldown {
            background: linear-gradient(to bottom, #888, #666);
            border-color: #555;
            cursor: not-allowed;
            pointer-events: none;
            min-width: 52px;
            text-align: center;
        }
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

        /* Spellbook redesign: preserve the game's parchment and wooden-frame visual language. */
        body {
            background: #e9dfcf url({{ asset('img/bg/bgg.gif') }}) repeat;
            color: #4a2a16;
        }
        .spellbook {
            max-width: 1180px;
            margin: 0 auto;
            padding: 12px;
            box-sizing: border-box;
        }
        .spellbook-hero {
            position: relative;
            display: grid;
            grid-template-columns: 190px minmax(0, 1fr);
            grid-template-rows: auto auto;
            column-gap: 12px;
            overflow: hidden;
            margin: 0 0 12px;
            padding: 7px 14px;
            color: #592a12;
            border: 1px solid #b57a4c;
            border-radius: 5px;
            border-bottom: 1px solid #c58c59;
            background: linear-gradient(to bottom, rgba(255, 241, 195, .9), rgba(223, 177, 119, .72));
            box-shadow: inset 0 1px rgba(255, 255, 255, .65);
        }
        .spellbook-hero:before {
            position: absolute;
            bottom: 3px;
            left: 12px;
            width: 40px;
            height: 20px;
            content: '';
            pointer-events: none;
            background: url({{ asset('main/images/magic_sep.png') }}) left top no-repeat;
        }
        .spellbook-title-plate {
            grid-row: 1 / span 2;
            width: 190px;
            height: 47px;
            align-self: center;
            box-sizing: border-box;
            padding: 12px 12px 0;
            background: url({{ asset('main/images/magic_backing.png') }}) center no-repeat;
        }
        .spellbook-title {
            margin: 0;
            color: #6a200e;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 15px;
            letter-spacing: .2px;
            text-align: center;
            text-shadow: 0 1px #ffe8bd;
            white-space: nowrap;
        }
        .spellbook-subtitle {
            align-self: end;
            margin: 0;
            color: #6a4125;
            font-size: 11px;
        }
        .spellbook-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 5px;
        }
        .spellbook-stat {
            padding: 3px 7px 1px;
            color: #ffe9ba;
            border: 1px solid #743216;
            border-radius: 10px;
            background: #8b3517;
            font-size: 10px;
            line-height: 12px;
            font-weight: bold;
        }
        .spell-section {
            margin: 0 0 12px;
            border: 1px solid #c08a59;
            border-radius: 5px;
            background: rgba(255, 248, 216, .35) url({{ asset('img/bg/common-bg.png') }}) repeat;
            box-shadow: inset 0 0 0 2px rgba(255, 246, 209, .48);
        }
        .section-header {
            position: relative;
            gap: 7px;
            margin: 0;
            padding: 7px 10px 6px;
            border: 0;
            border-bottom: 1px solid #c58c59;
            background: linear-gradient(to bottom, rgba(255, 241, 195, .9), rgba(223, 177, 119, .72));
            box-shadow: inset 0 1px rgba(255, 255, 255, .65);
        }
        .section-header:after {
            position: absolute;
            top: 50%;
            right: 9px;
            width: 43px;
            height: 20px;
            content: '';
            margin-top: -10px;
            background: url({{ asset('main/images/magic_sep.png') }}) right top no-repeat;
        }
        .section-header:before {
            width: 22px;
            height: 20px;
            content: '';
            background: url({{ asset('main/images/magic_sep.png') }}) 0 0 no-repeat;
        }
        .section-header span {
            color: #54230e;
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 13px;
            letter-spacing: .2px;
            text-shadow: 0 1px rgba(255, 255, 255, .6);
        }
        .section-count {
            min-width: 14px;
            padding: 1px 5px;
            color: #ffe9ba !important;
            border: 1px solid #743216;
            border-radius: 10px;
            background: #8b3517;
            font-family: Tahoma, sans-serif !important;
            font-size: 10px !important;
            line-height: 13px;
            text-align: center;
            text-shadow: none !important;
        }
        .skill-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(255px, 1fr));
            gap: 9px;
            padding: 10px;
        }
        .skill-card {
            width: auto;
            min-height: 155px;
            border: 1px solid #a8693f;
            border-radius: 4px;
            background: #f7e9bc url({{ asset('img/bg/bgg.gif') }}) repeat;
            box-shadow: 0 1px 2px rgba(72, 31, 10, .23), inset 0 0 0 2px rgba(255, 251, 221, .44);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .skill-card:hover {
            z-index: 1;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(72, 31, 10, .28), inset 0 0 0 2px rgba(255, 251, 221, .5);
        }
        .skill-card-header {
            min-height: 45px;
            padding: 5px 7px 5px 5px;
            color: #ffe6b3;
            border-bottom-color: #6d2c14;
            background: #ff000021;
        }
        .skill-icon {
            display: flex;
            width: 38px;
            height: 38px;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: #ffe6b3;
            border: 1px solid #d9a66d;
            border-radius: 3px;
            background: #3b160c;
            box-shadow: inset 0 0 0 2px rgba(0, 0, 0, .22);
            font-family: Georgia, serif;
            font-size: 23px;
            flex: 0 0 38px;
        }
        .skill-icon img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .skill-icon--attack { color: #ffd09c; background: #742111; }
        .skill-icon--buff { color: #d9f4b0; background: #2d6122; }
        .skill-icon--heal { color: #c6f5ff; background: #17616d; }
        .skill-card-name { margin-left: 5px; color: #5a260f; font-size: 12px; }
        a.skill-card-name { text-decoration: none; }
        a.skill-card-name:hover { color: #9a3014; text-decoration: underline; }
        .mana-badge { padding: 2px 5px; border: 1px solid #7ea8d6; background: #315e94; box-shadow: inset 0 1px rgba(255,255,255,.22); }
        .skill-type-badge { padding: 2px 5px; border: 1px solid rgba(255,255,255,.26); border-radius: 8px; }
        .drag-handle { order: 4; margin-left: 3px; color: #5b1a0d; }
        .skill-card-body { padding: 7px 8px; }
        .skill-dmg { margin-bottom: 5px; padding: 3px 5px; border-left: 3px solid #a83c22; background: rgba(145, 56, 23, .08); }
        .skill-dmg-value { color: #8d2616; font-size: 12px; }
        .skill-desc { min-height: 30px; margin-bottom: 6px; color: #5d4530; line-height: 1.35; }
        .skill-effect-row { padding: 2px 0; border-bottom: 1px dotted rgba(138, 91, 51, .35); }
        .skill-effect-name { color: #346c22; }
        .skill-cooldown-info { margin: 5px 0; color: #765236; }
        .skill-equip-row, .skill-use-row { border-top-color: #d3a270; }
        .skill-equip-row { margin-top: 5px; }
        .skill-use-row { display: flex; flex-wrap: wrap; gap: 5px; align-items: center; }
        .skill-use-row select { flex: 1 1 100%; border-color: #ba8554; background: #fff3cf; }
        .btn-use { padding: 3px 11px; border-color: #315f1d; border-radius: 2px; background: linear-gradient(to bottom, #79a94c, #3e7722); box-shadow: inset 0 1px rgba(255,255,255,.28); }
        .passive-list { gap: 7px; padding: 10px; }
        .passive-card { padding: 7px 9px; border-color: #b47a4c; border-radius: 3px; background: rgba(255, 248, 214, .65); box-shadow: inset 0 0 0 1px rgba(255,255,255,.55); }
        .passive-icon { width: 11px; height: 11px; margin-top: 2px; border: 1px solid #9f5a27; background: radial-gradient(circle at 35% 35%, #fff3af, #cf752f); box-shadow: 0 0 3px rgba(173,82,26,.65); }
        .passive-name { color: #5a260f; }
        a.passive-name { text-decoration: none; }
        a.passive-name:hover { color: #9a3014; text-decoration: underline; }
        .effect-badge { border: 1px solid #215d16; background: #337b24; box-shadow: inset 0 1px rgba(255,255,255,.2); }
        .empty-state { padding: 18px 10px; color: #806144; }
        @media (max-width: 640px) {
            .spellbook { padding: 6px; }
            .spellbook-hero { grid-template-columns: 1fr; row-gap: 5px; }
            .spellbook-title-plate { grid-row: auto; justify-self: center; }
            .spellbook-subtitle { text-align: center; }
            .spellbook-stats { justify-content: center; }
            .skill-grid { grid-template-columns: 1fr; padding: 7px; }
            .spellbook-title { font-size: 17px; }
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
                        @include('player::partials.tabs', ['group' => $page->group])
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0;">

                        @php
                            $combatSkills = $page->activeSkills->filter(fn($s) => !$s->isBuffSkill());
                            $buffSkills   = $page->activeSkills->filter(fn($s) => $s->isBuffSkill());
                        @endphp

                        <div class="spellbook">
                            <div class="spellbook-hero">
                                <div class="spellbook-title-plate">
                                    <h1 class="spellbook-title">Книга заклинаний</h1>
                                </div>
                                <p class="spellbook-subtitle">Настройте боевые приёмы и управляйте магией в бою.</p>
                                <div class="spellbook-stats">
                                    <span class="spellbook-stat">Боевых: {{ $combatSkills->count() }}</span>
                                    <span class="spellbook-stat">Поддержки: {{ $buffSkills->count() }}</span>
                                    <span class="spellbook-stat">Пассивных: {{ $page->passiveSkills->count() + $page->runePassives->count() }}</span>
                                </div>
                            </div>

                        {{-- Combat skills --}}
                        <section class="spell-section">
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
                                            <span class="skill-icon skill-icon--attack">
                                                @if($skill->image)
                                                    <img src="{{ $skill->image }}" alt="">
                                                @else
                                                    ✦
                                                @endif
                                            </span>
                                            <span class="drag-handle" title="Перетащить">⠿</span>
                                            <a href="{{ route('magic_skill.info', $skill->id) }}" class="skill-card-name" title="{{ $skill->name }}"
                                               onclick="window.open(this.href, '', 'width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $skill->name }}</a>
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

                        @endif
                        </section>

                        {{-- Buff / Heal skills --}}
                        <section class="spell-section">
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
                                            <span class="skill-icon {{ $skill->type === 'heal' ? 'skill-icon--heal' : 'skill-icon--buff' }}">
                                                @if($skill->image)
                                                    <img src="{{ $skill->image }}" alt="">
                                                @else
                                                    {{ $skill->type === 'heal' ? '✚' : '✹' }}
                                                @endif
                                            </span>
                                            <span class="drag-handle" title="Перетащить">⠿</span>
                                            <a href="{{ route('magic_skill.info', $skill->id) }}" class="skill-card-name" title="{{ $skill->name }}"
                                               onclick="window.open(this.href, '', 'width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $skill->name }}</a>
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
                                            @foreach($skill->skillEffects as $effect)
                                                <div class="skill-effect-row">
                                                    <span class="skill-effect-name">{{ $effect->name }}</span>
                                                    @if($effect->pivot->duration_seconds > 0)
                                                        <span class="skill-effect-duration">{{ format_cooldown($effect->pivot->duration_seconds) }}</span>
                                                    @endif
                                                    @if($effect->pivot->chance < 100)
                                                        <span class="skill-effect-chance">{{ $effect->pivot->chance }}%</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($skill->cooldown > 0)
                                                <div class="skill-cooldown-info">
                                                    ⏱ Кулдаун: {{ format_cooldown($skill->cooldown) }}
                                                </div>
                                            @endif
                                            <div class="skill-use-row">
                                                @if($page->allyTargets->isNotEmpty() && $skill->target_type === 'all')
                                                    <select id="target_{{ $skill->id }}">
                                                        <option value="">— Себе —</option>
                                                        @foreach($page->allyTargets as $ally)
                                                            <option value="{{ $ally->id }}">{{ $ally->user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @endif
                                                @php
                                                    $cooldownEnd = $skill->pivot->cooldown_end_at
                                                        ? \Carbon\Carbon::parse($skill->pivot->cooldown_end_at)->getTimestamp()
                                                        : 0;
                                                @endphp
                                                <a id="btn-skill-{{ $skill->id }}"
                                                   class="btn-use"
                                                   data-cooldown-end="{{ $cooldownEnd }}"
                                                   onclick="useSkill({{ $skill->id }}, '{{ route('magic_skill.use', $skill->id) }}')">Применить</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        </section>

                        {{-- Passive skills --}}
                        <section class="spell-section">
                        <div class="section-header" style="margin-top: 4px;">
                            <span>Пассивные навыки</span>
                            <span class="section-count">{{ $page->passiveSkills->count() }}</span>
                        </div>

                        @if($page->passiveSkills->isEmpty())
                            <div class="empty-state">Нет пассивных навыков</div>
                        @else
                            @php
                                $effectLabels = [
                                    'strength'     => 'Сила',
                                    'intuition'    => 'Интуиция',
                                    'agility'      => 'Ловкость',
                                    'intelligence' => 'Интеллект',
                                    'wisdom'       => 'Мудрость',
                                    'hp_max'       => 'Макс. HP',
                                    'mp_max'       => 'Макс. MP',
                                    'attack'       => 'Атака',
                                    'defense'      => 'Защита',
                                ];
                            @endphp
                            <div class="passive-list">
                                @foreach($page->passiveSkills as $skill)
                                    <div class="passive-card">
                                        <div class="passive-icon"></div>
                                        <div>
                                            <div>
                                                <a href="{{ route('magic_skill.info', $skill->id) }}" class="passive-name"
                                                   onclick="window.open(this.href, '', 'width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $skill->name }}</a>
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
                        </section>

                        {{-- Пассивки от рун --}}
                        <section class="spell-section">
                        <div class="section-header" style="margin-top: 4px;">
                            <span>Пассивки от рун</span>
                            <span class="section-count">{{ $page->runePassives->count() }}</span>
                        </div>

                        @if($page->runePassives->isEmpty())
                            <div class="empty-state">Нет активных пассивок от рун</div>
                        @else
                            <div class="passive-list">
                                @foreach($page->runePassives as $runePassive)
                                    <div class="passive-card">
                                        <div class="passive-icon"></div>
                                        <div>
                                            <div>
                                                <a href="{{ route('items.info.share', ['id' => $runePassive['runeShareItemId']]) }}" class="passive-name"
                                                   title="Открыть информацию о руне «{{ $runePassive['runeName'] }}»"
                                                   onclick="window.open(this.href, '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $runePassive['label'] }}</a>
                                                <span class="passive-desc">{{ $runePassive['description'] }}</span>
                                            </div>
                                            <div class="passive-effects">
                                                <span class="effect-badge">{{ $runePassive['runeName'] }} ({{ $runePassive['itemName'] }})</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        </section>
                        </div>

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

    var _cdTimers = {};

    function startCooldown(skillId, untilTimestamp) {
        var btn = document.getElementById('btn-skill-' + skillId);
        if (!btn || !untilTimestamp) return;

        if (_cdTimers[skillId]) clearInterval(_cdTimers[skillId]);

        function tick() {
            var remaining = Math.ceil(untilTimestamp - Date.now() / 1000);
            if (remaining <= 0) {
                clearInterval(_cdTimers[skillId]);
                delete _cdTimers[skillId];
                btn.classList.remove('on-cooldown');
                btn.textContent = 'Применить';
                return;
            }
            btn.classList.add('on-cooldown');
            btn.textContent = remaining + 'с';
        }

        tick();
        _cdTimers[skillId] = setInterval(tick, 1000);
    }

    function useSkill(skillId, url) {
        var btn = document.getElementById('btn-skill-' + skillId);
        if (btn && btn.classList.contains('on-cooldown')) return;

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
            if (data.hp && data.mp) {
                window.parent.sendToFrame('character-frame', { hp: data.hp, mp: data.mp });
            }
            if (data.cooldown_until) {
                startCooldown(skillId, data.cooldown_until);
            }
            if (data.blessings && data.blessings.length) {
                window.parent.sendToFrame('character-frame', { appliedEffects: data.blessings });
            }
        })
        .catch(() => window.parent.showErrorIframe('Ошибка при применении'));
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-cooldown-end]').forEach(function (btn) {
            var until = parseInt(btn.getAttribute('data-cooldown-end'));
            if (until > 0) {
                var skillId = btn.id.replace('btn-skill-', '');
                startCooldown(skillId, until);
            }
        });
    });

    function updateEquippedSkills(input) {
        const label = input.closest('.equip-label');
        const previousChecked = !input.checked;
        const inputs = Array.from(document.querySelectorAll('.combo-in-fight'));
        const params = { skills: [] };

        inputs.forEach(el => {
            if (el.checked) params.skills.push(el.value);
        });

        inputs.forEach(el => { el.disabled = true; });
        if (label) label.classList.add('is-saving');

        fetch('{{ route('magic_skill.update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(params)
        })
        .then(async r => {
            const data = await r.json();

            if (!r.ok) throw new Error(data.message || 'Ошибка при сохранении');

            return data;
        })
        .catch(error => {
            input.checked = previousChecked;
            window.parent.showErrorIframe(error.message || 'Ошибка при сохранении');
        })
        .finally(() => {
            inputs.forEach(el => { el.disabled = false; });
            if (label) label.classList.remove('is-saving');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.combo-in-fight').forEach(function (input) {
            input.addEventListener('change', function () {
                updateEquippedSkills(input);
            });
        });
    });

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
