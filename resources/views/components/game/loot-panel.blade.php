@props([
    'items',
    'count',
    'locationUrl',
    'message' => '',
    'backpackUrl' => null,
    'emptyMessage' => 'На земле ничего не осталось.',
])

<style>
    .loot-frame {
        /*width: 100%;*/
        /*max-width: 760px;*/
        /*margin: 0 auto;*/
        border-collapse: separate;
        border-spacing: 0;
        empty-cells: show;
    }

    .loot-frame td {
        padding: 0;
        border: 0;
        line-height: 0;
        vertical-align: top;
    }

    .loot-frame .tbl-shp-sml.lt,
    .loot-frame .tbl-shp-sml.rt,
    .loot-frame .tbl-shp-sml.lb,
    .loot-frame .tbl-shp-sml.rb,
    .loot-frame .tbl-shp-sides {
        width: 20px;
        min-width: 20px;
        max-width: 20px;
    }

    .loot-frame .tbl-shp-sides.ls {
        background-position: left top;
        background-repeat: repeat-y;
    }

    .loot-frame .tbl-shp-sides.rs {
        background-position: right top;
        background-repeat: repeat-y;
    }

    .loot-frame .tbl-shp-sml.rt {
        height: 22px;
        background-position: 0 -25px;
    }

    .loot-frame .tbl-shp-sml.tt {
        height: 22px;
        background-position: center -50px;
        background-repeat: repeat-x;
    }

    .loot-frame .tbl-shp-sml.lt {
        height: 22px;
        background-position: 0 0;
    }

    .loot-frame .tbl-shp-sml.lb {
        background-position: 0 -75px;
    }

    .loot-frame .tbl-shp-sml.bb {
        height: 18px;
        background-position: center -125px;
        background-repeat: repeat-x;
    }

    .loot-frame .tbl-shp-sml.rb {
        background-position: 0 -100px;
    }

    .loot-frame .tbl-shp-sml {
        background-image: url('{{ asset('img/bg/tbl-shp-sml.png') }}');
        background-repeat: no-repeat;
        font-size: 0;
    }

    .loot-frame .tbl-shp-sides {
        width: 20px;
        background-image: url('{{ asset('img/bg/tbl-shp-sides.png') }}');
        background-repeat: no-repeat;
        font-size: 0;
    }

    .loot-frame .loot-surface {
        padding: 2px 6px 7px;
        background: url('{{ asset('img/bg/tbl-usi_bg.gif') }}') repeat;
        line-height: normal;
    }

    .loot-board {
        overflow: hidden;
        border: 1px solid #b89470;
        background: #fff7e9;
        box-shadow: 0 1px 4px rgba(72, 38, 14, .2), inset 0 0 18px rgba(187, 126, 68, .08);
    }

    .loot-header {
        display: flex;
        align-items: center;
        min-height: 58px;
        padding: 7px 12px;
        border-bottom: 1px solid #c69d74;
        background:
            linear-gradient(90deg, rgba(255, 244, 213, .88), rgba(231, 175, 119, .65)),
            url('{{ asset('img/bg/tbl-usi_bg.gif') }}') repeat;
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, .6);
    }

    .loot-emblem {
        width: 57px;
        height: 57px;
        flex: 0 0 57px;
        margin-right: 10px;
        display: block;
        background-image: url('{{ asset('data/canvas/ui/main.png') }}');
        background-repeat: no-repeat;
        background-position: -63px -375px;
    }

    .loot-heading {
        min-width: 0;
        flex: 1;
    }

    .loot-kicker {
        margin-bottom: 2px;
        color: #98643e;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .7px;
        text-transform: uppercase;
    }

    .loot-title {
        color: #461c0b;
        font-size: 14px;
        font-weight: 700;
        line-height: 17px;
        text-shadow: 0 1px 0 #fff2d0;
    }

    .loot-subtitle {
        margin-top: 2px;
        color: #79553c;
        font-size: 10px;
        line-height: 14px;
    }

    .loot-total {
        min-width: 30px;
        margin-left: 12px;
        padding: 4px 7px;
        border: 1px solid #a87850;
        border-radius: 3px;
        background: linear-gradient(#fff2cb, #e9bd82);
        color: #562710;
        font-size: 12px;
        font-weight: 700;
        text-align: center;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .8);
    }

    .loot-message {
        margin: 8px 9px 0;
        padding: 7px 9px;
        border: 1px solid #a8b985;
        background: linear-gradient(#f7f4d7, #e6e9c2);
        color: #45532a;
        font-size: 11px;
        line-height: 15px;
        text-align: center;
    }

    .loot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(245px, 1fr));
        gap: 8px;
        padding: 9px;
    }

    .loot-card {
        display: flex;
        min-width: 0;
        min-height: 70px;
        padding: 7px;
        border: 1px solid #ceb091;
        background: linear-gradient(135deg, #fffdf7, #f8e8cf);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .7), 0 1px 2px rgba(83, 45, 17, .12);
        transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
    }

    .loot-card:hover {
        border-color: #a8754b;
        box-shadow: inset 0 0 0 1px #fff6df, 0 2px 5px rgba(83, 45, 17, .2);
        transform: translateY(-1px);
    }

    .loot-icon {
        position: relative;
        width: 56px;
        height: 56px;
        flex: 0 0 56px;
        margin-right: 9px;
        border: 1px solid #ad865f;
        background: #ead4b4;
        box-shadow: inset 0 0 0 2px #fff3dc, 0 1px 2px rgba(65, 33, 12, .25);
    }

    .loot-icon > img {
        display: block;
        width: 50px;
        height: 50px;
        margin: 3px;
        object-fit: contain;
    }

    .loot-quantity {
        position: absolute;
        right: -5px;
        bottom: -5px;
        min-width: 18px;
        padding: 2px 4px;
        border: 1px solid #74401e;
        border-radius: 8px;
        background: #5e2d12;
        color: #ffe9b7;
        font-size: 9px;
        font-weight: 700;
        line-height: 11px;
        text-align: center;
        box-shadow: 0 1px 2px rgba(43, 21, 7, .35);
    }

    .loot-card-content {
        display: flex;
        min-width: 0;
        flex: 1;
        flex-direction: column;
        justify-content: space-between;
    }

    .loot-item-name {
        overflow: hidden;
        margin: 1px 0 3px;
        color: #4c210d;
        font-size: 11px;
        font-weight: 700;
        line-height: 14px;
        text-overflow: ellipsis;
    }

    .loot-item-hint {
        color: #8a6a4f;
        font-size: 9px;
        line-height: 12px;
    }

    .loot-card-action {
        margin-top: 6px;
        text-align: right;
    }

    .loot-empty {
        padding: 26px 16px 28px;
        color: #775b45;
        text-align: center;
    }

    .loot-empty-title {
        margin-bottom: 4px;
        color: #572b14;
        font-size: 12px;
        font-weight: 700;
    }

    .loot-empty-text {
        color: #91745d;
        font-size: 10px;
    }

    .loot-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 8px 10px 9px;
        border-top: 1px solid #c69d74;
        background:
            linear-gradient(90deg, rgba(255, 244, 213, .88), rgba(231, 175, 119, .65)),
            url('{{ asset('img/bg/tbl-usi_bg.gif') }}') repeat;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .6);
        text-align: center;
    }

    @media (max-width: 560px) {
        .loot-grid {
            grid-template-columns: 1fr;
        }

        .loot-subtitle {
            display: none;
        }

        .loot-footer {
            flex-wrap: wrap;
        }
    }
</style>

<table class="loot-frame" role="presentation" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td width="20" class="tbl-shp-sml lt"></td>
        <td class="tbl-shp-sml tt"></td>
        <td width="20" class="tbl-shp-sml rt"></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls"></td>
        <td class="loot-surface">
            <section class="loot-board">
                <header class="loot-header">
                    <span class="loot-emblem" aria-hidden="true"></span>
                    <div class="loot-heading">
                        <div class="loot-kicker">Найденная добыча</div>
                        <div class="loot-title">Предметы на локации</div>
                        <div class="loot-subtitle">Выберите предмет, который хотите забрать с собой</div>
                    </div>
                    <div class="loot-total" title="Количество найденных позиций">{{ $count }}</div>
                </header>

                @if($message !== '')
                    <div class="loot-message">{!! $message !!}</div>
                @endif

                @if($items !== [])
                    <div class="loot-grid">
                        @foreach($items as $item)
                            <article class="loot-card">
                                <div class="loot-icon">
                                    <img src="{{ $item->image }}" alt="{{ $item->name }}">
                                    <span class="loot-quantity">×{{ $item->count }}</span>
                                </div>
                                <div class="loot-card-content">
                                    <div>
                                        <div class="loot-item-name" title="{{ $item->name }}">{{ $item->name }}</div>
                                        <div class="loot-item-hint">
                                            {{ $item->actionLabel === 'Поднять'
                                                ? 'Предмет можно забрать в рюкзак'
                                                : 'Сундук можно открыть и осмотреть' }}
                                        </div>
                                    </div>
                                    <div class="loot-card-action">
                                        <b class="butt2 pointer"><b>
                                            <input value="{{ $item->actionLabel }}" type="button" onclick="window.location.href = @js($item->actionUrl)">
                                        </b></b>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="loot-empty">
                        <div class="loot-empty-title">Добыча уже собрана</div>
                        <div class="loot-empty-text">{{ $emptyMessage }}</div>
                    </div>
                @endif

                <footer class="loot-footer">
                    <b class="butt2 pointer"><b>
                        <input value="« Описание местности" type="button" onclick="window.parent.sendDataToGame(@js($locationUrl))">
                    </b></b>
                    @if($backpackUrl !== null)
                        <b class="butt2 pointer"><b>
                            <input value="Рюкзак" type="button" onclick="window.parent.sendDataToGame(@js($backpackUrl))">
                        </b></b>
                    @endif
                </footer>
            </section>
        </td>
        <td class="tbl-shp-sides rs"></td>
    </tr>
    <tr>
        <td width="20" class="tbl-shp-sml lb"></td>
        <td class="tbl-shp-sml bb"></td>
        <td width="20" class="tbl-shp-sml rb"></td>
    </tr>
    </tbody>
</table>
