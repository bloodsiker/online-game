<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Апгрейд</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { min-height: 100%; margin: 0; font-family: Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .tbl-usi_bg { background: url('/img/bg/tbl-usi_bg.gif') repeat; }
        .brd2-all { border: 1px solid #db9f73; }
        .bg_l { background-image: url('/img/bg/info/bg_l.gif'); }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; }
        .pointer, .pointer input { cursor: pointer; }

        .upgrade-summary { margin-bottom: 10px; }
        .upgrade-hint { margin: 0 0 10px; color: #49382d; text-align: center; }
        .upgrade-grid { margin: -6px; font-size: 0; text-align: center; }
        .upgrade-card { display: inline-block; vertical-align: top; width: 270px; min-height: 340px; margin: 6px; padding: 8px; box-sizing: border-box; font-size: 11px; text-align: center; background: url('/img/bg/bgg.gif') repeat; border-radius: 5px; box-shadow: 0 0 3px rgba(0,0,0,.9); }
        .upgrade-title { min-height: 30px; color: #7a3010; font-size: 13px; font-weight: bold; }
        .upgrade-level { display: block; margin-top: 2px; color: #8d2616; font-weight: bold; }
        .upgrade-icons { display: flex; align-items: center; justify-content: center; gap: 7px; margin: 7px 0 5px; }
        .upgrade-icon { display: inline-block; width: 60px; height: 60px; padding: 5px 6px 6px; background: url('/main/images/user-reward-frame.png') no-repeat; cursor: pointer; }
        .upgrade-icon img { width: 60px; height: 60px; object-fit: contain; }
        .upgrade-arrow { color: #8d2616; font-size: 24px; font-weight: bold; }
        .upgrade-names { min-height: 34px; margin: 4px 0 7px; color: #49382d; line-height: 15px; }
        .upgrade-names strong { font-weight: bold; }
        .upgrade-requirements { min-height: 104px; padding: 5px; box-sizing: border-box; text-align: left; background: url('/img/bg/tbl-usi_bg.gif') repeat; border: 2px solid #e3b360; border-radius: 5px; line-height: 16px; }
        .requirements-title { color: #553e20; font-weight: bold; text-align: center; }
        .requirement-items { display: flex; flex-wrap: wrap; justify-content: center; gap: 5px; margin-top: 4px; }
        .requirement-item { width: 48px; text-align: center; line-height: 12px; cursor: pointer; }
        .requirement-item img { width: 36px; height: 36px; object-fit: contain; border: 1px solid #9a713e; vertical-align: middle; background: url('/img/bg/empty_slot.gif') center / 36px 36px; }
        .requirement-name { display: block; margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 10px; }
        .requirement-count { display: block; margin-top: 1px; font-size: 10px; white-space: nowrap; }
        .req-ok { color: #247327 !important; font-weight: bold; }
        .req-fail { color: #a02020 !important; font-weight: bold; }
        .upgrade-action { margin-top: 7px; }
        .butt2.disabled, .butt2.disabled input { cursor: default; opacity: .55; }
        .message { display: inline-block; margin: 0 0 8px; padding: 4px 8px; border: 1px solid; font-weight: bold; }
        .message.success { color: #247327; border-color: #247327; }
        .message.error { color: #a02020; border-color: #a02020; }
        .empty-list { padding: 25px; color: #49382d; font-size: 12px; text-align: center; }
    </style>
</head>
<body>

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('blacksmith::_tabs', ['activeTab' => 'rarity-upgrade'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:8px 10px; text-align:center;">
            <table class="coll brd2-all bg_l p10h p2v upgrade-summary" width="100%">
                <tbody><tr>
                    <td align="left"><b>Кузня:</b> Апгрейд</td>
                    <td align="right" style="color:#955c4a;">
                        <b>Монеты:</b>
                        <b class="redd"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->money) }}</b>
                        &nbsp;&nbsp;<b>Бриллианты:</b>
                        <b class="redd"><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->diamond) }}</b>
                    </td>
                </tr></tbody>
            </table>

            @if(session('message'))
                <div class="message {{ session('rarity_upgrade_success', false) ? 'success' : 'error' }}">{{ session('message') }}</div>
            @endif

            <p class="upgrade-hint">Выберите вещь для повышения редкости. Заточка, камни и руны сохраняются.</p>

            <div class="upgrade-grid">
                @forelse($items as $item)
                    <div class="upgrade-card">
                        <div class="upgrade-title">
                            {{ $item['targetName'] }}
                            <span class="upgrade-level">{{ $item['rarity'] }} → {{ $item['targetRarity'] }}</span>
                        </div>

                        <div class="upgrade-icons">
                            <span class="upgrade-icon"
                                  data-id="{{ $item['itemId'] }}"
                                  onmouseover="showItemInfo(this,event,2)"
                                  onmouseout="showItemInfo(this,event,0)">
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                            </span>
                            <span class="upgrade-arrow">→</span>
                            <span class="upgrade-icon"
                                  data-id="{{ $item['targetId'] }}"
                                  onmouseover="showItemInfo(this,event,2)"
                                  onmouseout="showItemInfo(this,event,0)">
                                <img src="{{ $item['targetImage'] }}" alt="{{ $item['targetName'] }}">
                            </span>
                        </div>

                        <div class="upgrade-names">
                            <strong style="color:{{ $item['rarityColor'] }}">{{ $item['name'] }}</strong><br>
                            <span style="color:#8d2616">превратится в</span><br>
                            <strong style="color:{{ $item['targetRarityColor'] }}">{{ $item['targetName'] }}</strong>
                        </div>

                        <div class="upgrade-requirements">
                            <div class="requirements-title">Требования для апгрейда</div>
                            <div>
                                Монеты:
                                <span class="{{ $user->money >= $item['gold'] ? 'req-ok' : 'req-fail' }}">
                                    {{ format_money($user->money) }} / {{ format_money($item['gold']) }}
                                </span>
                            </div>

                            @if(count($item['materials']) > 0)
                                <div class="requirement-items">
                                    @foreach($item['materials'] as $material)
                                        <div class="requirement-item" title="{{ $material['name'] }}"
                                             data-id="{{ $material['id'] }}"
                                             onmouseover="showItemInfo(this,event,2)"
                                             onmouseout="showItemInfo(this,event,0)">
                                            <img src="{{ $material['image'] }}" alt="{{ $material['name'] }}">
                                            <span class="requirement-name">{{ $material['name'] }}</span>
                                            <span class="requirement-count {{ $material['available'] >= $material['needed'] ? 'req-ok' : 'req-fail' }}">
                                                {{ $material['available'] }} / {{ $material['needed'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($item['gold'] === 0)
                                <div class="req-ok" style="text-align:center; margin-top:5px;">Дополнительные ресурсы не требуются</div>
                            @endif
                        </div>

                        <form class="upgrade-action" action="{{ route('blacksmith.rarity_upgrade.process', ['id' => $blacksmith->id]) }}" method="post">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item['itemId'] }}">
                            <b class="butt2 pointer {{ $item['canUpgrade'] ? '' : 'disabled' }}">
                                <b><input type="submit" value="Улучшить" @disabled(! $item['canUpgrade'])></b>
                            </b>
                        </form>
                    </div>
                @empty
                    <div class="empty-list"><b>В рюкзаке нет вещей с настроенным апгрейдом редкости.</b></div>
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
    function equalizeUpgradeRequirements() {
        const requirements = Array.from(document.querySelectorAll('.upgrade-requirements'));
        if (requirements.length === 0) return;

        requirements.forEach((element) => { element.style.height = ''; });
        const maxHeight = Math.max(...requirements.map((element) => element.offsetHeight));
        requirements.forEach((element) => { element.style.height = maxHeight + 'px'; });
    }

    window.addEventListener('load', equalizeUpgradeRequirements);
    window.addEventListener('resize', equalizeUpgradeRequirements);
</script>
{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
@if(session()->has('message'))
    <script>window.parent.showErrorIframe(@json(session('message')));</script>
@endif
</body>
</html>
