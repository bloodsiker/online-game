<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $structure->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        body { margin: 8px; color: #461c0b; font: 11px Tahoma, sans-serif; }
        .prison-layout { display: flex; align-items: flex-start; gap: 16px; }
        .prison-main { flex: 1 1 auto; min-width: 0; }
        .prison-side { flex: 0 0 240px; width: 240px; }
        .prison-table { width: 100%; border-collapse: collapse; }
        .prison-table th, .prison-table td { padding: 6px 8px; border: 1px solid #db9f73; text-align: left; }
        .prison-table th { background: #f3d6b7 url(/img/bg/info/bg_l.gif) repeat; }
        .prison-empty { padding: 10px; border: 1px solid #db9f73; background: #fff8ec; text-align: center; }
        .prison-release { white-space: nowrap; color: #8b1e16; font-weight: bold; }
        .prison-art { display: block; width: 197px; max-width: 100%; height: auto; margin: 0 auto; image-rendering: pixelated; }
        .prison-description { padding: 4px 6px; }
        .common-inset-tl { background: url('/main/images/common-inset-tl.png') no-repeat; }
        .common-inset-tr { background: url('/main/images/common-inset-tr.png') no-repeat; }
        .common-inset-bl { background: url('/main/images/common-inset-bl.png') no-repeat; }
        .common-inset-br { background: url('/main/images/common-inset-br.png') no-repeat; }
        .common-inset-t { background: url('/main/images/common-inset-t.png') repeat-x; }
        .common-inset-b { background: url('/main/images/common-inset-b.png') repeat-x; }
        .common-inset-l { background: url('/main/images/common-inset-l.png') repeat-y; }
        .common-inset-r { background: url('/main/images/common-inset-r.png') repeat-y; }
        .common-inset-bg { background: #ffffd6; }
        .prison-buyout-content { padding: 8px 12px; text-align: center; }
        @media (max-width: 600px) {
            .prison-layout { flex-direction: column; }
            .prison-main, .prison-side { flex: 0 0 auto; width: 100%; }
            .prison-side > table { width: min(240px, 100%); }
        }
    </style>
</head>
<body>
<div class="prison-layout">
    <main class="prison-main">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>
            <tr height="22">
                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                <td class="tbl-shp-sml tt" valign="top" align="center">
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                        <tr height="22">
                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                            <td align="center" class="tbl-usi_label-center">Заключённые</td>
                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
            </tr>
            <tr>
                <td class="tbl-shp-sides ls">&nbsp;</td>
                <td class="tbl-usi_bg" valign="top" style="padding: 8px 10px">
                    @if(session('message'))
                        <div class="prison-empty" style="margin-bottom: 10px">{{ session('message') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="prison-empty" style="margin-bottom: 10px; color: #a00">{{ $errors->first() }}</div>
                    @endif
                    <p style="margin: 0 0 10px; text-align: center;">Здесь содержатся игроки, отбывающие назначенное наказание.</p>

                    @if($activeDetention)
                        <table class="w100" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 10px">
                            <tbody>
                            <tr>
                                <td width="12" height="12" class="common-inset-tl"></td>
                                <td height="12" class="common-inset-t"></td>
                                <td width="12" height="12" class="common-inset-tr"></td>
                            </tr>
                            <tr>
                                <td width="12" class="common-inset-l"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                <td class="common-inset-bg">
                                    <div class="prison-buyout-content">
                                        Вы отбываете наказание до <b class="prison-release">{{ $activeDetention->release_at->format('d.m.Y H:i') }}</b>.
                                        До освобождения перемещение и игровые действия недоступны.
                                    </div>
                                </td>
                                <td width="12" class="common-inset-r"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                            </tr>
                            <tr>
                                <td width="12" height="12" class="common-inset-bl"></td>
                                <td height="12" class="common-inset-b"></td>
                                <td width="12" height="12" class="common-inset-br"></td>
                            </tr>
                            </tbody>
                        </table>
                        @php($buyoutAmounts = $activeDetention->currentBuyoutAmounts())
                        @if($buyoutAmounts['money'] > 0 || $buyoutAmounts['diamond'] > 0)
                            <table class="w100" border="0" cellpadding="0" cellspacing="0" style="margin-bottom: 10px">
                                <tbody>
                                <tr>
                                    <td width="12" height="12" class="common-inset-tl"></td>
                                    <td height="12" class="common-inset-t"></td>
                                    <td width="12" height="12" class="common-inset-tr"></td>
                                </tr>
                                <tr>
                                    <td width="12" class="common-inset-l"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                    <td class="common-inset-bg">
                                        <div class="prison-buyout-content" id="prison-buyout" data-starts-at="{{ $activeDetention->starts_at->timestamp }}" data-release-at="{{ $activeDetention->release_at->timestamp }}" data-fine-money="{{ (int) $activeDetention->fine_money }}" data-fine-diamond="{{ (float) $activeDetention->fine_diamond }}">
                                            <div>Вы можете выкупиться. Сумма уменьшается по мере отбытия срока:</div>
                                            <div style="margin: 7px 0">
                                                <b id="prison-buyout-money" @if($buyoutAmounts['money'] <= 0) style="display:none" @endif><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle" alt=""> <span>{{ number_format($buyoutAmounts['money'], 0, '', ' ') }}</span></b>
                                                <b id="prison-buyout-diamond" @if($buyoutAmounts['diamond'] <= 0) style="display:none" @endif><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" align="absmiddle" alt=""> <span>{{ number_format($buyoutAmounts['diamond'], 2, '.', ' ') }}</span></b>
                                            </div>
                                            <form method="post" action="{{ route('prison.buyout', ['id' => $structure->id]) }}">
                                                @csrf
                                                <span class="butt1 pointer"><span><input type="submit" class="grnn" value="Выкупиться"></span></span>
                                            </form>
                                        </div>
                                    </td>
                                    <td width="12" class="common-inset-r"><img src="{{ asset('main/images/blank.gif') }}" width="12" alt=""></td>
                                </tr>
                                <tr>
                                    <td width="12" height="12" class="common-inset-bl"></td>
                                    <td height="12" class="common-inset-b"></td>
                                    <td width="12" height="12" class="common-inset-br"></td>
                                </tr>
                                </tbody>
                            </table>
                        @endif
                    @endif

                    @if($detainees->isEmpty())
                        <div class="prison-empty">Сейчас в Темнице никого нет.</div>
                    @else
                        <table class="prison-table" cellspacing="0" cellpadding="0">
                            <thead>
                            <tr><th>Персонаж</th><th>Наказание</th><th>Освобождение</th></tr>
                            </thead>
                            <tbody>
                            @foreach($detainees as $detention)
                                <tr>
                                    <td>
                                        @php($clan = $detention->user?->clanMembership?->clan)
                                        @if($clan?->icon)
                                            <a href="{{ route('clan.public', ['clan' => $clan->id]) }}" title="{{ $clan->name }}" onclick="window.open(this.href, '', 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">
                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($clan->icon) }}" alt="{{ $clan->name }}" width="13" height="13" align="absmiddle">
                                            </a>
                                        @endif
                                        <b>{{ $detention->user?->name ?? $detention->user_name }}</b>@if($detention->user?->player) [{{ $detention->user->player->lvl }}]@endif
                                        @if($detention->user)
                                            <a href="{{ route('info.user', ['id' => $detention->user->id]) }}" title="{{ __('Информация о персонаже') }}" onclick="window.open(this.href, '', 'width=930,height=800,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">
                                                <img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" align="absmiddle" alt="{{ __('Информация о персонаже') }}">
                                            </a>
                                        @endif
                                    </td>
                                    <td>{{ $detention->reason }}</td>
                                    <td class="prison-release">{{ $detention->release_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
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
    </main>

    <aside class="prison-side">
        <table width="240" border="0" cellspacing="0" cellpadding="0">
            <tbody>
            <tr height="22">
                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                <td class="tbl-shp-sml tt" valign="top" align="center">
                    <table border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                        <tr height="22">
                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                            <td align="center" class="tbl-usi_label-center">{{ $structure->name }}</td>
                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
            </tr>
            <tr>
                <td class="tbl-shp-sides ls">&nbsp;</td>
                <td class="tbl-usi_bg" valign="top" style="padding: 4px 0">
                    <img class="prison-art" src="{{ $structure->image ?: asset('data/canvas/ui/dungeon.webp') }}" alt="{{ $structure->name }}">
                    @if($structure->description)
                        <div class="p2v prison-description">{!! nl2br(e((string) $structure->description)) !!}</div>
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
    </aside>
</div>
<script>
    (() => {
        const box = document.getElementById('prison-buyout');
        if (!box) return;

        const startsAt = Number(box.dataset.startsAt);
        const releaseAt = Number(box.dataset.releaseAt);
        const totalSeconds = Math.max(1, releaseAt - startsAt);
        const fineMoney = Number(box.dataset.fineMoney);
        const fineDiamond = Number(box.dataset.fineDiamond);

        const update = () => {
            const remaining = Math.max(0, releaseAt - Math.floor(Date.now() / 1000));
            const money = fineMoney > 0 && remaining > 0 ? Math.min(fineMoney, Math.max(1, Math.ceil(fineMoney * remaining / totalSeconds))) : 0;
            const diamond = fineDiamond > 0 && remaining > 0 ? Math.min(fineDiamond, Math.max(0.01, Math.ceil(fineDiamond * remaining / totalSeconds * 100) / 100)) : 0;
            const moneyRow = document.getElementById('prison-buyout-money');
            const diamondRow = document.getElementById('prison-buyout-diamond');

            if (moneyRow) {
                moneyRow.style.display = money > 0 ? '' : 'none';
                moneyRow.querySelector('span').textContent = new Intl.NumberFormat('ru-RU', { maximumFractionDigits: 0 }).format(money);
            }
            if (diamondRow) {
                diamondRow.style.display = diamond > 0 ? '' : 'none';
                diamondRow.querySelector('span').textContent = new Intl.NumberFormat('ru-RU', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(diamond);
            }
        };

        update();
        window.setInterval(update, 1000);
    })();
</script>
</body>
</html>
