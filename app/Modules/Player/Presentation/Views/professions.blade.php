<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профессии</title>
    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body { margin: 0; color: #4d3c31; font: 11px Tahoma, Arial, sans-serif; }
        a { color: #765039; }
        .tbl-shp-sml { background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sides { background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat; font-size: 0; }
        .tbl-usi_bg { background: #e8dac7 url({{ asset('img/bg/tbl-usi_bg.gif') }}) repeat; }
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
        .professions-wrap { padding: 10px; }
        .profession-tabs { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; padding: 6px; border: 1px solid #bca68e; background: rgba(245, 236, 223, .78); }
        .profession-tab { border: 1px solid #a98d6c; border-radius: 2px; padding: 5px 9px; color: #563e2f; background: linear-gradient(#f5ead6, #d8c19e); font: bold 11px Tahoma, sans-serif; cursor: pointer; }
        .profession-tab:hover { background: linear-gradient(#fff4df, #e1c9a4); }
        .profession-tab.active { color: #fff0c7; border-color: #75472e; background: linear-gradient(#9b6442, #704029); text-shadow: 0 1px #3b2115; }
        .profession-count { display: inline-block; min-width: 16px; margin-left: 4px; padding: 0 4px; border-radius: 8px; color: #6d4b36; background: rgba(255,255,255,.6); text-align: center; font-size: 9px; }
        .profession-tab.active .profession-count { color: #633b25; background: #f2d8a4; }
        .profession-panel { display: none; }
        .profession-panel.active { display: block; }
        .profession-summary { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border: 1px solid #bca68e; border-radius: 3px; background: #f4ecdf url({{ asset('img/bg/common-bg.png') }}) repeat; }
        .profession-level { flex: 0 0 68px; height: 68px; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px solid #a88661; border-radius: 50%; color: #fff1c4; background: radial-gradient(circle at 35% 30%, #b57b4d, #71432b 70%); box-shadow: inset 0 0 0 3px rgba(255,235,189,.18); }
        .profession-level b { font-size: 21px; line-height: 20px; }
        .profession-level span { font-size: 9px; text-transform: uppercase; }
        .profession-info { flex: 1; min-width: 0; }
        .profession-name { color: #5c2813; font-size: 15px; font-weight: bold; }
        .profession-description { min-height: 17px; margin: 3px 0 7px; color: #756256; }
        .profession-progress-head { display: flex; justify-content: space-between; margin-bottom: 3px; color: #6d5a4e; font-size: 10px; }
        .profession-progress { height: 10px; overflow: hidden; border: 1px solid #9f856d; border-radius: 5px; background: #d0c0ad; box-shadow: inset 0 1px 2px rgba(50,30,20,.18); }
        .profession-progress-fill { height: 100%; background: linear-gradient(90deg, #95633e, #d29450, #bc7840); }
        .recipes-heading { display: flex; align-items: center; gap: 6px; margin: 12px 2px 7px; padding-bottom: 4px; border-bottom: 1px solid #c4a886; color: #5a2a0a; font-size: 12px; font-weight: bold; }
        .recipes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(265px, 1fr)); gap: 8px; }
        .recipe-card { display: flex; min-height: 96px; border: 1px solid #b99c72; border-radius: 3px; overflow: hidden; background: #f7eedb; box-shadow: 0 1px 2px rgba(66,45,30,.1); }
        .recipe-icon { flex: 0 0 76px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #dfccaa, #f3e6cd); border-right: 1px solid #c7aa82; }
        .recipe-icon img { width: 58px; height: 58px; object-fit: contain; }
        .recipe-body { flex: 1; min-width: 0; padding: 7px 8px; }
        .recipe-name { color: #6e160f; font-weight: bold; text-decoration: none; }
        .recipe-name:hover { text-decoration: underline; }
        .recipe-description { margin: 4px 0 6px; color: #766357; line-height: 1.3; }
        .recipe-meta { display: flex; flex-wrap: wrap; gap: 4px 9px; color: #806650; font-size: 10px; }
        .recipe-result { display: flex; align-items: center; gap: 5px; margin-top: 6px; padding-top: 5px; border-top: 1px solid #decdb4; }
        .recipe-result img { width: 28px; height: 28px; object-fit: contain; }
        .recipe-result a { color: #5f3f2e; font-weight: bold; text-decoration: none; }
        .empty-recipes { padding: 24px; border: 1px dashed #b9a187; color: #77675b; background: rgba(255,248,234,.6); text-align: center; }
        .empty-page { padding: 35px; text-align: center; color: #75644d; }
    </style>
</head>
<body>
<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody><tr valign="top"><td>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>
            <tr height="22">
                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                <td class="tbl-shp-sml tt" valign="top" align="left">@include('player::partials.tabs', ['group' => 'professions'])</td>
                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
            </tr>
            <tr>
                <td class="tbl-shp-sides ls">&nbsp;</td>
                <td class="tbl-usi_bg" valign="top">
                    <main class="professions-wrap">
                        @if($page['professions'] === [])
                            <div class="empty-page">Мирные профессии ещё не настроены.</div>
                        @else
                            <nav class="profession-tabs" aria-label="Мирные профессии">
                                @foreach($page['professions'] as $profession)
                                    <button type="button"
                                            class="profession-tab @if($profession['id'] === $page['activeProfessionId']) active @endif"
                                            data-profession-tab="{{ $profession['id'] }}"
                                            aria-selected="{{ $profession['id'] === $page['activeProfessionId'] ? 'true' : 'false' }}">
                                        {{ $profession['name'] }} <span class="profession-count">{{ $profession['recipesCount'] }}</span>
                                    </button>
                                @endforeach
                            </nav>

                            @foreach($page['professions'] as $profession)
                                <section class="profession-panel @if($profession['id'] === $page['activeProfessionId']) active @endif" data-profession-panel="{{ $profession['id'] }}">
                                    <header class="profession-summary">
                                        <div class="profession-level"><b>{{ $profession['level'] }}</b><span>уровень</span></div>
                                        <div class="profession-info">
                                            <div class="profession-name">{{ $profession['name'] }}</div>
                                            <div class="profession-description">{{ $profession['description'] ?: 'Мирная профессия.' }}</div>
                                            <div class="profession-progress-head">
                                                <span>Прогресс текущего уровня</span>
                                                <b>{{ $profession['experiencePercent'] }}%</b>
                                            </div>
                                            <div class="profession-progress" title="{{ $profession['levelExperience'] }} / {{ $profession['levelExperienceRequired'] }}">
                                                <div class="profession-progress-fill" style="width: {{ $profession['experiencePercent'] }}%"></div>
                                            </div>
                                        </div>
                                    </header>

                                    <div class="recipes-heading">Изученные рецепты <span class="profession-count">{{ $profession['recipesCount'] }}</span></div>
                                    @if($profession['recipes'] === [])
                                        <div class="empty-recipes">У этой профессии пока нет изученных рецептов.</div>
                                    @else
                                        <div class="recipes-grid">
                                            @foreach($profession['recipes'] as $recipe)
                                                <article class="recipe-card">
                                                    <div class="recipe-icon"><img src="{{ asset($recipe['image']) }}" alt="{{ $recipe['name'] }}"></div>
                                                    <div class="recipe-body">
                                                        <a class="recipe-name" href="{{ route('items.info.share', ['id' => $recipe['shareItemId']]) }}" target="_blank">{{ $recipe['name'] }}</a>
                                                        @if($recipe['description'] !== '')<div class="recipe-description">{{ $recipe['description'] }}</div>@endif
                                                        <div class="recipe-meta">
                                                            <span>Требуется: <b>{{ $recipe['requiredLevel'] }} ур.</b></span>
                                                            <span>Опыт: <b>+{{ $recipe['experience'] }}</b></span>
                                                            @if($recipe['learnedAt'])<span>Изучен: <b>{{ $recipe['learnedAt'] }}</b></span>@endif
                                                        </div>
                                                        <div class="recipe-result">
                                                            <span>Результат:</span>
                                                            <img src="{{ asset($recipe['resultImage']) }}" alt="">
                                                            <a href="{{ route('items.info.share', ['id' => $recipe['resultShareItemId']]) }}" target="_blank">{{ $recipe['resultName'] }}</a>
                                                        </div>
                                                    </div>
                                                </article>
                                            @endforeach
                                        </div>
                                    @endif
                                </section>
                            @endforeach
                        @endif
                    </main>
                </td>
                <td class="tbl-shp-sides rs">&nbsp;</td>
            </tr>
            <tr height="18"><td class="tbl-shp-sml lb"></td><td class="tbl-shp-sml bb"></td><td class="tbl-shp-sml rb"></td></tr>
            </tbody>
        </table>
    </td></tr></tbody>
</table>
<script>
    document.querySelectorAll('[data-profession-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            var professionId = tab.dataset.professionTab;
            document.querySelectorAll('[data-profession-tab]').forEach(function (item) {
                var active = item === tab;
                item.classList.toggle('active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            document.querySelectorAll('[data-profession-panel]').forEach(function (panel) {
                panel.classList.toggle('active', panel.dataset.professionPanel === professionId);
            });
            history.replaceState(null, '', '{{ route('character.professions') }}?profession=' + encodeURIComponent(professionId));
        });
    });
</script>
</body>
</html>
