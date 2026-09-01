<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Разбор предметов</title>
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

        .break-summary { margin-bottom: 10px; }
        .break-hint { margin: 0 0 10px; color: #49382d; text-align: center; }
        .break-grid { margin: -6px; font-size: 0; text-align: center; }
        .break-card { display: inline-block; vertical-align: top; width: 270px; min-height: 320px; margin: 6px; padding: 8px; box-sizing: border-box; font-size: 11px; text-align: center; background: url('/img/bg/bgg.gif') repeat; border-radius: 5px; box-shadow: 0 0 3px rgba(0,0,0,.9); }
        .break-title { min-height: 30px; color: #7a3010; font-size: 13px; font-weight: bold; }
        .break-yield { display: block; margin-top: 2px; color: #8d2616; font-weight: bold; }
        .break-icons { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 7px 0 5px; }
        .upgrade-icon { display: inline-block; width: 60px; height: 60px; padding: 5px 6px 6px; background: url('/main/images/user-reward-frame.png') no-repeat; cursor: pointer; }
        .upgrade-icon img { width: 60px; height: 60px; object-fit: contain; }
        .break-arrow { color: #8d2616; font-size: 24px; font-weight: bold; }
        .break-names { min-height: 34px; margin: 4px 0 7px; color: #49382d; line-height: 15px; }
        .break-names strong { font-weight: bold; }
        .break-result { min-height: 104px; padding: 5px; box-sizing: border-box; text-align: left; background: url('/img/bg/tbl-usi_bg.gif') repeat; border: 2px solid #e3b360; border-radius: 5px; line-height: 16px; }
        .result-title { color: #553e20; font-weight: bold; text-align: center; }
        .result-item { display: flex; align-items: center; justify-content: center; gap: 7px; margin-top: 6px; cursor: pointer; }
        .result-item img { width: 36px; height: 36px; object-fit: contain; border: 1px solid #9a713e; background: url('/img/bg/empty_slot.gif') center / 36px 36px; }
        .result-details { min-width: 105px; color: #49382d; line-height: 14px; text-align: left; }
        .result-count { display: block; color: #247327; font-weight: bold; }
        .break-warning { margin-top: 5px; color: #a02020; font-size: 10px; text-align: center; }
        .break-action { margin-top: 7px; }
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
            @include('blacksmith::_tabs', ['activeTab' => 'break'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:8px 10px; text-align:center;">
            <table class="coll brd2-all bg_l p10h p2v break-summary" width="100%">
                <tbody><tr>
                    <td align="left"><b>Кузня:</b> Разбить предмет</td>
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

            <p class="break-hint">Разберите ненужный предмет и получите кристаллы.</p>

            <div class="break-grid">
                @forelse($items as $item)
                    <div class="break-card">
                        <div class="break-title">
                            {{ $item['name'] }}
                            <span class="break-yield">Результат: {{ $item['breakCrystal'] }} шт.</span>
                        </div>

                        <div class="break-icons">
                            <span class="upgrade-icon"
                                  data-id="{{ $item['itemId'] }}"
                                  onmouseover="showItemInfo(this,event,2)"
                                  onmouseout="showItemInfo(this,event,0)">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                            </span>
                            <span class="break-arrow">→</span>
                            <span class="upgrade-icon"
                                  data-id="{{ $crystal->id }}"
                                  onmouseover="showItemInfo(this,event,2)"
                                  onmouseout="showItemInfo(this,event,0)">
                                <img src="{{ $crystal->transparent_image ?? $crystal->image }}" alt="{{ $crystal->name }}">
                            </span>
                        </div>

                        <div class="break-names">
                            <strong style="color:{{ $item['rarityColor'] }}">{{ $item['name'] }}</strong><br>
                            <span style="color:#8d2616">будет разобран на</span><br>
                            <strong style="color:{{ $crystal->rarity->color() }}">{{ $crystal->name }}</strong>
                        </div>

                        <div class="break-result">
                            <div class="result-title">Результат разборки</div>
                            <div class="result-item"
                                 data-id="{{ $crystal->id }}"
                                 onmouseover="showItemInfo(this,event,2)"
                                 onmouseout="showItemInfo(this,event,0)">
                                <img src="{{ $crystal->transparent_image ?? $crystal->image }}" alt="{{ $crystal->name }}">
                                <span class="result-details">
                                    {{ $crystal->name }}
                                    <span class="result-count">{{ $item['breakCrystal'] }} шт.</span>
                                </span>
                            </div>
                            <div class="break-warning">Предмет будет уничтожен без возможности восстановления.</div>
                        </div>

                        <div class="break-action">
                            <b class="butt2 pointer">
                                <b>
                                    <input type="button"
                                           class="break-item"
                                           value="Разобрать"
                                           data-href="{{ route('blacksmith.break', ['id' => $blacksmith->id, 'iid' => $item['itemId']]) }}">
                                </b>
                            </b>
                        </div>
                    </div>
                @empty
                    <div class="empty-list"><b>В рюкзаке нет предметов, доступных для разбора.</b></div>
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
    function equalizeBreakResults() {
        const results = Array.from(document.querySelectorAll('.break-result'));
        if (results.length === 0) return;

        results.forEach((element) => { element.style.height = ''; });
        const maxHeight = Math.max(...results.map((element) => element.offsetHeight));
        results.forEach((element) => { element.style.height = maxHeight + 'px'; });
    }

    document.querySelectorAll('.break-item[data-href]').forEach(function (button) {
        button.addEventListener('click', function () {
            window.location.href = this.dataset.href;
        });
    });

    window.addEventListener('load', equalizeBreakResults);
    window.addEventListener('resize', equalizeBreakResults);
</script>

{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
@if(session()->has('message'))
    <script>window.parent.showErrorIframe(@json(session('message')));</script>
@endif
</body>
</html>
