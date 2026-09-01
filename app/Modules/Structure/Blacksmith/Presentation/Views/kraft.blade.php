<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .butt2.disabled, .butt2.disabled input { cursor: default; opacity: .55; }
        .message { display: inline-block; margin: 0 0 8px; padding: 4px 8px; border: 1px solid #8d2616; color: #8d2616; font-weight: bold; }
        .empty-list { padding: 25px; color: #49382d; font-size: 12px; text-align: center; }
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

            <p class="craft-hint">Выберите доступный рецепт и создайте предмет из собранных материалов.</p>

            <div class="craft-grid">
                @forelse($recipes as $recipe)
                    <div class="craft-card">
                        <div class="craft-title">
                            {{ $recipe['resultName'] }}
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
                            <span class="upgrade-icon"
                                  data-id="{{ $recipe['resultId'] }}"
                                  onmouseover="showItemInfo(this,event,2)"
                                  onmouseout="showItemInfo(this,event,0)">
                                <img src="{{ $recipe['resultImage'] }}" alt="{{ $recipe['resultName'] }}">
                            </span>
                        </div>

                        <div class="craft-names">
                            <strong style="color:{{ $recipe['recipeRarityColor'] }}">{{ $recipe['recipeName'] }}</strong><br>
                            <span style="color:#8d2616">создаёт</span><br>
                            <strong style="color:{{ $recipe['resultRarityColor'] }}">{{ $recipe['resultName'] }}</strong>
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
                            <b class="butt2 pointer {{ $recipe['canCraft'] ? '' : 'disabled' }}">
                                <b>
                                    <input type="button"
                                           class="kraft-item"
                                           value="Создать"
                                           @if($recipe['canCraft'])
                                               data-href="{{ route('blacksmith.kraft', ['id' => $recipe['recipeItemId']]) }}"
                                           @else
                                               disabled
                                           @endif>
                                </b>
                            </b>
                        </div>
                    </div>
                @empty
                    <div class="empty-list"><b>В рюкзаке нет доступных рецептов для крафта.</b></div>
                @endforelse
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
@if(session()->has('message'))
    <script>window.parent.showErrorIframe(@json(session('message')));</script>
@endif
</body>
</html>
