<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Крафт</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { min-height: 100%; margin: 0; font-family: Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .tbl-usi_bg { background: url('/img/bg/tbl-usi_bg.gif') repeat; }
        .brd2-all { border: 1px solid #db9f73; }
        .bg_l { background-image: url('/img/bg/info/bg_l.gif'); }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .pointer, .pointer input { cursor: pointer; }

        .craft-summary { margin-bottom: 10px; }
        .craft-hint { margin: 0 0 10px; color: #49382d; text-align: center; }
        .craft-grid { margin: -6px; font-size: 0; text-align: center; }
        .craft-card { display: inline-block; vertical-align: top; width: 270px; min-height: 340px; margin: 6px; padding: 8px; box-sizing: border-box; font-size: 11px; text-align: center; background: url('/img/bg/bgg.gif') repeat; border-radius: 5px; box-shadow: 0 0 3px rgba(0,0,0,.9); }
        .craft-title { min-height: 30px; color: #7a3010; font-size: 13px; font-weight: bold; }
        .craft-chance { display: block; margin-top: 2px; color: #8d2616; font-weight: bold; }
        .craft-icons { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 7px 0 5px; }
        .upgrade-icon { display: inline-block; width: 60px; height: 60px; padding: 5px 6px 6px; background: url('/main/images/user-reward-frame.png') no-repeat; cursor: pointer; }
        .upgrade-icon img { width: 60px; height: 60px; object-fit: contain; }
        .craft-arrow { color: #8d2616; font-size: 24px; font-weight: bold; }
        .craft-names { min-height: 34px; margin: 4px 0 7px; color: #49382d; line-height: 15px; }
        .craft-names strong { font-weight: bold; }
        .random-result-icon { display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold; color: #8d2616; }
        .result-pool-list { margin-top: 4px; text-align: left; }
        .result-pool-item { display: flex; align-items: center; gap: 4px; padding: 2px 0; cursor: pointer; }
        .result-pool-item img { width: 20px; height: 20px; object-fit: contain; }
        .result-pool-item span:first-of-type { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: bold; }
        .result-pool-chance { color: #8d2616; font-weight: bold; white-space: nowrap; }
        .craft-requirements { min-height: 104px; padding: 5px; box-sizing: border-box; text-align: left; background: url('/img/bg/tbl-usi_bg.gif') repeat; border: 2px solid #e3b360; border-radius: 5px; line-height: 16px; }
        .requirements-title { color: #553e20; font-weight: bold; text-align: center; }
        .requirement-items { display: flex; flex-wrap: wrap; justify-content: center; gap: 5px; margin-top: 4px; }
        .requirement-item { width: 48px; text-align: center; line-height: 12px; cursor: pointer; }
        .requirement-item img { width: 36px; height: 36px; object-fit: contain; border: 1px solid #9a713e; vertical-align: middle; background: url('/img/bg/empty_slot.gif') center / 36px 36px; }
        .requirement-name { display: block; margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 10px; }
        .requirement-count { display: block; margin-top: 1px; font-size: 10px; white-space: nowrap; }
        .req-ok { color: #247327 !important; font-weight: bold; }
        .req-fail { color: #a02020 !important; font-weight: bold; }
        .craft-action { margin-top: 7px; }
        .butt1.pointer.disabled { cursor: default; opacity: .5; }
        .butt1.pointer.disabled input { cursor: default; }
        .message { display: inline-block; margin: 0 0 8px; padding: 4px 8px; border: 1px solid #8d2616; color: #8d2616; font-weight: bold; }
        .empty-list { padding: 25px; color: #49382d; font-size: 12px; text-align: center; }
        .craft-layout { display: flex; align-items: flex-start; gap: 0; }
        .craft-menu { flex: 0 0 92px; width: 92px; margin-right: -6px; text-align: center; }
        .craft-menu a { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; box-sizing: content-box; width: 88px; height: 58px; padding: 10px 2px; color: #49382d; font-weight: bold; text-decoration: none; background: url('/main/images/set-tabs.png') 0 100% no-repeat; }
        .craft-menu a[aria-current="page"] { z-index: 3; color: #8d2616; background-position: 0 0; }
        .craft-content { position: relative; z-index: 2; flex: 1; min-width: 0; }
        .craft-container { width: 100%; table-layout: fixed; border-spacing: 0; }
        .craft-container .common-inner-block-tl { background: url('/main/images/common-inner-block-tl.png') no-repeat; }
        .craft-container .common-inner-block-tr { background: url('/main/images/common-inner-block-tr.png') no-repeat; }
        .craft-container .common-inner-block-bl { background: url('/main/images/common-inner-block-bl.png') no-repeat; }
        .craft-container .common-inner-block-br { background: url('/main/images/common-inner-block-br.png') no-repeat; }
        .craft-container .common-inner-block-t { background: url('/main/images/common-inner-block-t.png') repeat-x; }
        .craft-container .common-inner-block-b { background: url('/main/images/common-inner-block-b.png') repeat-x; }
        .craft-container .common-inner-block-l { background: url('/main/images/common-inner-block-l.png') repeat-y; }
        .craft-container .common-inner-block-r { background: url('/main/images/common-inner-block-r.png') repeat-y; }
        .craft-container .common-inner-block-bg { background: url('/img/bg/bg_l.gif') repeat; padding: 3px; vertical-align: top; }
        .artifact-assembly { width: 100%; table-layout: fixed; border-spacing: 0; margin-bottom: 12px; }
        .artifact-assembly .gp-frame-tl, .artifact-assembly .gp-frame-tr, .artifact-assembly .gp-frame-bl, .artifact-assembly .gp-frame-br { background: url('/img/bg/common-corners.png') no-repeat; font-size: 0; line-height: 0; }
        .artifact-assembly .gp-frame-t, .artifact-assembly .gp-frame-b { background: url('/img/bg/common-tb.png') repeat-x; font-size: 0; line-height: 0; }
        .artifact-assembly .gp-frame-l, .artifact-assembly .gp-frame-r { background: url('/img/bg/common-lr.png') repeat-y; font-size: 0; line-height: 0; }
        .artifact-assembly .gp-frame-tl, .artifact-assembly .gp-frame-t, .artifact-assembly .gp-frame-l { background-position: 0 0; }
        .artifact-assembly .gp-frame-tr, .artifact-assembly .gp-frame-r { background-position: 100% 0; }
        .artifact-assembly .gp-frame-bl, .artifact-assembly .gp-frame-b { background-position: 0 100%; }
        .artifact-assembly .gp-frame-br { background-position: 100% 100%; }
        .artifact-assembly .gp-frame-bg { background: url('/img/bg/common-bg.png') repeat; padding: 10px; }
        .craft-hint-frame .craft-hint { margin: 0; line-height: 1.5; }
        .artifact-assembly__row { display: flex; align-items: center; justify-content: center; gap: 16px; overflow-x: auto; padding-bottom: 5px; }
        .artifact-assembly__ingredients { display: flex; flex-wrap: nowrap; gap: 6px; }
        .artifact-assembly__ingredient { flex: 0 0 72px; width: 72px; min-height: 88px; text-align: center; }
        .artifact-assembly .upgrade-icon { box-sizing: content-box; flex: 0 0 auto; text-decoration: none; }
        .artifact-assembly__count { display: block; margin-top: 4px; font-weight: bold; white-space: nowrap; }
        .artifact-assembly__equals { flex: 0 0 auto; align-self: flex-start; display: flex; align-items: center; height: 71px; font-size: 28px; font-weight: bold; color: #8d2616; }
        .artifact-assembly__result { display: flex; align-items: center; gap: 14px; flex: 0 0 auto; text-align: center; }
        .artifact-assembly__result-preview { width: 100px; min-height: 88px; }
        .artifact-assembly__result > .butt1 { align-self: flex-start; margin-top: 24px; }
        .artifact-assembly__result .random-result-icon { margin: 0 auto; }
        .artifact-assembly__result-name { margin: 5px 0 8px; font-weight: bold; }
        .artifact-assembly__pool { min-width: 0; margin: 12px 0 0; padding: 10px 14px 14px; border: 1px solid #b88848; background: rgba(255, 244, 205, .65); box-shadow: inset 0 0 0 2px rgba(255, 255, 230, .6); text-align: center; }
        .artifact-assembly__pool legend { float: none; width: auto; margin: 0 auto; padding: 0 10px; color: #7a3010; font-size: 12px; font-weight: bold; }
        .artifact-assembly__pool-items { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 6px; }
        .artifact-assembly__pool-item { width: 90px; text-align: center; }
        .artifact-assembly__pool-item { padding: 5px; border: 2px solid transparent; border-radius: 4px; transition: background-color .06s, border-color .06s, box-shadow .06s; }
        .artifact-assembly__pool-item.is-highlighted { background: #ffe7a0; border-color: #c39237; box-shadow: 0 0 10px rgba(219, 170, 64, .75); }
        .artifact-assembly__pool-item.is-winner { background: #fff0b6; border-color: #a57413; box-shadow: 0 0 14px rgba(230, 176, 44, .9); }
        .artifact-assembly.is-assembling .artifact-assemble-button { cursor: wait; }
    </style>
</head>
<body>

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('blacksmith::_tabs', ['activeTab' => 'kraft'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:8px 10px; text-align:center;">
            <table class="coll brd2-all bg_l p10h p2v craft-summary" width="100%">
                <tbody><tr>
                    <td align="left"><b>Кузня:</b> Крафтить</td>
                    <td align="right" style="color:#955c4a;">
                        <b>Монеты:</b>
                        <b class="redd"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->money) }}</b>
                        &nbsp;&nbsp;<b>Бриллианты:</b>
                        <b class="redd"><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->diamond) }}</b>
                    </td>
                </tr></tbody>
            </table>

            @if(session('message'))
                <div class="message">{{ session('message') }}</div>
            @endif

            <div class="craft-layout">
                <nav class="craft-menu" aria-label="{{ __('Крафт') }}">
                    <a href="{{ route('blacksmith', ['id' => $blacksmith->id]) }}" @if($craftSection === 'craft') aria-current="page" @endif>{{ __('Крафт') }}</a>
                    <a href="{{ route('blacksmith', ['id' => $blacksmith->id, 'section' => 'artifacts']) }}" @if($craftSection === 'artifacts') aria-current="page" @endif>{{ __('Артефакты') }}</a>
                </nav>
                <div class="craft-content">
                    <table class="craft-container" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                        <tr>
                            <td width="10" height="10" class="common-inner-block-tl"></td>
                            <td height="10" class="common-inner-block-t"></td>
                            <td width="10" height="10" class="common-inner-block-tr"></td>
                        </tr>
                        <tr>
                            <td class="common-inner-block-l"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                            <td class="common-inner-block-bg">
                @if($craftSection === 'craft')
            @include('blacksmith::_craft-hint', ['hint' => __('Выберите доступный рецепт и создайте предмет из собранных материалов.')])

            <div class="craft-grid">
                @forelse($recipes as $recipe)
                    <div class="craft-card">
                        <div class="craft-title">
                            {{ $recipe['isRandomResult'] ? 'Случайный предмет' : $recipe['resultName'] }}
                            <span class="craft-chance">Шанс создания: {{ $recipe['chancePercent'] }}%</span>
                        </div>

                        <div class="craft-icons">
                            <span class="upgrade-icon"
                                  data-id="{{ $recipe['recipeItemId'] }}"
                                  onmouseover="showItemInfo(this,event,2)"
                                  onmouseout="showItemInfo(this,event,0)">
                                <img src="{{ $recipe['recipeImage'] }}" alt="{{ $recipe['recipeName'] }}">
                            </span>
                            <span class="craft-arrow">→</span>
                            @if($recipe['isRandomResult'])
                                <span class="upgrade-icon random-result-icon" title="Случайный предмет из пула">?</span>
                            @else
                                <span class="upgrade-icon"
                                      data-id="{{ $recipe['resultId'] }}"
                                      onmouseover="showItemInfo(this,event,2)"
                                      onmouseout="showItemInfo(this,event,0)">
                                    <img src="{{ $recipe['resultImage'] }}" alt="{{ $recipe['resultName'] }}">
                                </span>
                            @endif
                        </div>

                        <div class="craft-names">
                            <strong style="color:{{ $recipe['recipeRarityColor'] }}">{{ $recipe['recipeName'] }}</strong><br>
                            <span style="color:#8d2616">создаёт</span>
                            @if($recipe['isRandomResult'])
                                <span style="color:#8d2616">(один случайный предмет):</span>
                                <div class="result-pool-list">
                                    @foreach($recipe['resultPool'] as $poolItem)
                                        <div class="result-pool-item"
                                             data-id="{{ $poolItem['id'] }}"
                                             onmouseover="showItemInfo(this,event,2)"
                                             onmouseout="showItemInfo(this,event,0)">
                                            <img src="{{ $poolItem['image'] }}" alt="{{ $poolItem['name'] }}">
                                            <span style="color:{{ $poolItem['rarityColor'] }}">{{ $poolItem['name'] }}</span>
                                            <span class="result-pool-chance">{{ $poolItem['chancePercent'] }}%</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <br><strong style="color:{{ $recipe['resultRarityColor'] }}">{{ $recipe['resultName'] }}</strong>
                            @endif
                        </div>

                        <div class="craft-requirements">
                            <div class="requirements-title">Материалы для крафта</div>
                            @if(count($recipe['ingredients']) > 0)
                                <div class="requirement-items">
                                    @foreach($recipe['ingredients'] as $item)
                                        <div class="requirement-item" title="{{ $item['name'] }}"
                                             data-id="{{ $item['id'] }}"
                                             onmouseover="showItemInfo(this,event,2)"
                                             onmouseout="showItemInfo(this,event,0)">
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                                            <span class="requirement-name">{{ $item['name'] }}</span>
                                            <span class="requirement-count {{ $item['active'] ? 'req-ok' : 'req-fail' }}">
                                                {{ $item['availableCount'] }} / {{ $item['requiredCount'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="req-ok" style="margin-top:5px; text-align:center;">Материалы не требуются</div>
                            @endif
                        </div>

                        <div class="craft-action">
                            <span class="butt1 pointer {{ $recipe['canCraft'] ? '' : 'disabled' }}">
                                <span>
                                    <input type="button"
                                           class="grnn kraft-item"
                                           value="Создать"
                                           @if($recipe['canCraft'])
                                               data-href="{{ route('blacksmith.kraft', ['id' => $recipe['recipeItemId']]) }}"
                                           @else
                                               disabled
                                           @endif>
                                </span>
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="empty-list"><b>В рюкзаке нет доступных рецептов для крафта.</b></div>
                @endforelse
            </div>

                @else
                    @include('blacksmith::_artifact-assemblies')
                @endif
                            </td>
                            <td class="common-inner-block-r"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                        </tr>
                        <tr>
                            <td width="10" height="10" class="common-inner-block-bl"></td>
                            <td height="10" class="common-inner-block-b"></td>
                            <td width="10" height="10" class="common-inner-block-br"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18">
        <td width="20" class="tbl-shp-sml lb"><b></b></td>
        <td class="tbl-shp-sml bb">&nbsp;</td>
        <td width="20" class="tbl-shp-sml rb"><b></b></td>
    </tr>
    </tbody>
</table>

<script>
    function equalizeCraftRequirements() {
        const requirements = Array.from(document.querySelectorAll('.craft-requirements'));
        if (requirements.length === 0) return;

        requirements.forEach((element) => { element.style.height = ''; });
        const maxHeight = Math.max(...requirements.map((element) => element.offsetHeight));
        requirements.forEach((element) => { element.style.height = maxHeight + 'px'; });
    }

    document.querySelectorAll('.kraft-item[data-href]').forEach(function (button) {
        button.addEventListener('click', function () {
            window.location.href = this.dataset.href;
        });
    });

    window.addEventListener('load', equalizeCraftRequirements);
    window.addEventListener('resize', equalizeCraftRequirements);
</script>

{!! $playerStatsScript !!}
{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
@php
    $assemblyMessages = [
        'refreshFailed' => __('Не удалось обновить количество частей. Обновите страницу.'),
        'assemblyFailed' => __('Не удалось собрать артефакт.'),
        'resultChanged' => __('Артефакт получен. Обновите страницу, чтобы увидеть результат.'),
    ];
@endphp
<script>
    window.artifactAssemblyMessages = @json($assemblyMessages);
</script>
<script src="{{ asset('js/artifact-assembly.js') }}?v={{ filemtime(public_path('js/artifact-assembly.js')) }}"></script>
@if(session()->has('message'))
    <script>window.parent.showErrorIframe(@json(session('message')));</script>
@endif
</body>
</html>
