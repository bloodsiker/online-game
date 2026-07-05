<link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
<style>
    /* ===== Панель действий (левая) ===== */
    .fight-window { margin-bottom: 6px; }
    .fight-window .tbl-usi_bg { min-width: 208px; }
    .fight-body { padding: 8px 6px 10px; }

    .fight-act { margin-bottom: 2px; text-align: center; white-space: nowrap; }
    .fight-act button { width: 158px; white-space: nowrap; }
    .fight-act-flee { margin-top: 8px; }

    .fight-spell-label {
        margin: 8px 0 4px;
        color: #553987;
        font-size: 11px;
        font-weight: bold;
        text-align: center;
        text-shadow: 0 1px 0 rgba(255, 245, 220, .85);
    }

    .fight-mana {
        display: inline-block;
        min-width: 14px;
        margin-left: 4px;
        padding: 0 4px;
        color: #eef5ff !important;
        font-size: 10px;
        font-weight: normal;
        text-align: center;
        vertical-align: 1px;
        border: 1px solid #6d84c1;
        border-radius: 8px;
        background: linear-gradient(to bottom, #254baf, #132a71);
        box-shadow: inset 0 1px 0 rgba(194, 218, 255, .35);
    }

    /* Полосы HP/MP игрока — классы обновляются скриптом, не переименовывать */
    .act-stats { margin-top: 8px; padding-top: 6px; border-top: 1px solid #DB9F73; }
    .act-stat-row { display: flex; align-items: center; gap: 5px; margin-bottom: 4px; }
    .act-stat-label { width: 56px; flex-shrink: 0; color: #955c4a; font-size: 11px; }
    .act-stat-bar {
        flex: 1;
        min-width: 56px;
        height: 9px;
        padding: 1px;
        overflow: hidden;
        background: #6e534c;
        border: 1px solid #6f4a24;
        box-shadow: inset 0 1px 3px rgba(36, 18, 4, .65);
        box-sizing: border-box;
    }
    .act-stat-hp { height: 100%; background: linear-gradient(to bottom, #ff6a4a, #c11c05 55%, #8a1200); }
    .act-stat-mp { height: 100%; background: linear-gradient(to bottom, #6aa6ff, #1d55c1 55%, #10307a); }
    .act-stat-val { min-width: 46px; color: #461c0b; font-size: 10px; font-weight: bold; text-align: right; white-space: nowrap; }

    /* ===== Панель участников боя (правая) ===== */
    .bp-wrap { min-width: 165px; padding: 2px 2px 6px; font-size: 11px; }

    .bp-sec {
        margin: 4px 0 2px;
        padding: 2px 4px 3px;
        font-size: 11px;
        font-weight: bold;
        border-bottom: 1px solid #DB9F73;
        text-shadow: 0 1px 0 rgba(255, 244, 214, .8);
        white-space: nowrap;
    }
    .bp-sec-enemy { color: #BA0000; }
    .bp-sec-ally { color: #1a6a1a; margin-top: 10px; }

    .bp-unit { padding: 3px 4px; border-bottom: 1px solid #EDD5C3; white-space: nowrap; }
    .bp-unit-target {
        background: url({{ asset('img/bg/bg_l.gif') }});
        border-left: 2px solid #BA0000;
        padding-left: 3px;
    }
    .bp-target-arrow { color: #BA0000; font-size: 10px; margin-right: 2px; }

    .bp-unit-name a { color: #5a1f00; }
    .bp-unit-name a:hover { color: #8b2f00; }
    .bp-unit-name a.color-red { color: #BA0000; font-weight: bold; }
    .bp-unit-lvl { color: #857767; font-size: 10px; margin-left: 3px; }
    .bp-unit-time { color: #857767; font-size: 10px; margin-left: 4px; }

    .bp-hp-row { display: flex; align-items: center; gap: 4px; margin-top: 2px; }
    .bp-hp-bar {
        flex: 1;
        min-width: 56px;
        height: 7px;
        padding: 1px;
        overflow: hidden;
        background: #6e534c;
        border: 1px solid #6f4a24;
        box-shadow: inset 0 1px 2px rgba(36, 18, 4, .6);
        box-sizing: border-box;
    }
    .bp-hp-fill { height: 100%; background: linear-gradient(to bottom, #ff6a4a, #c11c05 55%, #8a1200); }
    .bp-hp-fill.hp-high { background: linear-gradient(to bottom, #ff6a4a, #c11c05 55%, #8a1200); }
    .bp-hp-fill.hp-mid  { background: linear-gradient(to bottom, #ffc36a, #d97806 55%, #9a5200); }
    .bp-hp-fill.hp-low  { background: linear-gradient(to bottom, #d24a35, #7c0d00 55%, #4d0800); }
    .bp-hp-fill.hp-ally { background: linear-gradient(to bottom, #7ec24a, #2f8212 55%, #1d5407); }
    .bp-mp-fill { height: 100%; background: linear-gradient(to bottom, #6aa6ff, #1d55c1 55%, #10307a); }
    .bp-hp-text { min-width: 52px; color: #857767; font-size: 9px; text-align: right; white-space: nowrap; }

    .bp-footer {
        margin-top: 8px;
        padding-top: 5px;
        border-top: 1px solid #DB9F73;
        font-size: 10px;
        text-align: center;
    }
    .bp-footer a { color: #5a1f00; font-weight: bold; }
    .bp-footer a:hover { color: #8b2f00; }
</style>