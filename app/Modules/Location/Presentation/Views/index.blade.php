<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('interface::partials.game-translations')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __("Игра") }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 14px; }
        a { color: #000000; }
        a:hover { color: #353434 }
        ul { padding-left: 0; }
        ul li { list-style-type: none; margin-bottom: 3px; }
        ul li a img { margin-right: 6px; border: 0; }
        .main-table { width: 100%; height: 100%; }
        .center { text-align: center; }
        .location-description { margin-top: 15px; margin-bottom: 10px; }
        .battle-description { margin-top: 15px; }
        .side-move { margin-top: 30px; }
        .side-move::after { content: ""; display: block; clear: both; }
        .color-red { color: red; }
        .bg { background-color: #000; background-image: url({{ asset('img/bg/bg.gif') }}); background-attachment: fixed; background-position: 0 5px; }
        .tbl-sts_top { background-image: url({{ asset('img/bg/tbl-sts_top.gif') }}); background-repeat: repeat-x; background-position: bottom; height: 19px; }
        .tbl-sts-bb { background: url({{ asset('img/bg/tbl-sts.png') }}) left top repeat-x; }
        .tbl-sts b { background: url({{ asset('img/bg/tbl-sts.png') }}) no-repeat; display: block; height: 19px; overflow: hidden; width: 19px; }
        .tbl-sts-lt b { background-position: 0 -50px; }
        .tbl-sts-rt b { background-position: 0 -100px; }
        .tbl-sts-lb b { background-position: 0 -170px; }
        .tbl-sts-rb b { background-position: 0 -219px; }
        .tbl-sts-ltb b { background-position: 0 -69px; height: 20px; }
        .tbl-sts-lbt b { background-position: 0 -150px; height: 20px; }
        .tbl-sts-rtb b { background-position: 0 -119px; height: 20px; }
        .tbl-sts-rbt b { background-position: 0 -200px; height: 20px; }
        .tbl-sts_left { background-image: url({{ asset('img/bg/tbl-sts_left.gif') }}); background-repeat: repeat-y; width: 19px; background-position: right; }
        .tbl-sts_right { background-image: url({{ asset('img/bg/tbl-sts_right.gif') }}); background-repeat: repeat-y; width: 19px; }
        .bgg { background-image: url({{ asset('img/bg/bgg.gif') }}); }
        .location-frame-top { background-image: url({{ asset('img/bg/info/tbl-shp_sml-top.gif') }}); background-repeat: repeat-x; height: 22px; font-size: 1px; }
        .location-frame-left { background-image: url({{ asset('img/bg/info/tbl-usi_left.gif') }}); background-repeat: repeat-y; background-position: right; width: 20px; }
        .location-frame-bg { background-image: url({{ asset('img/bg/info/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .location-frame-right { background-image: url({{ asset('img/bg/info/tbl-usi_right.gif') }}); background-repeat: repeat-y; width: 20px; }
        .location-frame-bottom { background-image: url({{ asset('img/bg/info/tbl-shp_sml-bottom.gif') }}); background-repeat: repeat-x; height: 18px; font-size: 1px; }
        span.butt1 a.button, span.butt1.disabled a.button {
            height: 38px;
            display: inline-block;
            line-height: 35px;
            border: 0;
            color: #f8dea4 !important;
            cursor: pointer;
            font-family: Tahoma;
            font-size: 11px !important;
            font-weight: 700;
            text-decoration: none;
            margin: 0 33px;
            background: transparent url({{ asset('img/bg/btn/tbl-btn2_center.png') }}) center top repeat-x;
            padding-bottom: 3px;
            outline: none;
        }
        .rep-divider {
            position: relative;
            width: 88%;
            height: 11px;
            margin: 7px auto 5px;
        }
        .rep-divider::before {
            position: absolute;
            top: 5px;
            right: 0;
            left: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #9a704f 18%, #9a704f 82%, transparent);
            content: '';
        }
        .rep-divider::after {
            position: absolute;
            top: 2px;
            left: 50%;
            width: 7px;
            height: 7px;
            border: 1px solid #895936;
            background: #d4aa70;
            content: '';
            transform: translateX(-50%) rotate(45deg);
        }

        /* --- Компас перемещения --- */
        .compass-wrap { width: 215px; display: flex; flex-direction: column; align-items: center; }
        .compass {
            position: relative;
            width: 120px;
            height: 120px;
            background-image: url({{ asset('img/compass-rose.png') }});
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }
        .compass-dir {
            position: absolute;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Tahoma;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: none;
            color: #4a2e12;
            text-shadow: 0 1px 0 rgba(255,235,190,.75), 0 -1px 1px rgba(0,0,0,.35);
            background: transparent;
            border: 1px solid transparent;
            transition: background .15s ease, box-shadow .15s ease, transform .15s ease, border-color .15s ease;
        }
        .compass-dir.compass-disabled {
            color: #baae99;
            text-shadow: none;
            cursor: default;
            pointer-events: none;
        }
        .compass-dir-north { top: -32px; left: 50%; transform: translateX(-50%); }
        .compass-dir-south { bottom: -32px; left: 50%; transform: translateX(-50%); }
        .compass-dir-west { left: -46px; top: 49%; transform: translateY(-50%); }
        .compass-dir-east { top: 49%; transform: translateY(-50%); }
        html[lang="ru"] .compass-dir-east { right: -49px; }
        html[lang="uk"] .compass-dir-east { right: -43px; }
        /* Вверх/вниз — по диагонали справа: между севером и востоком, между востоком и югом. */
        .compass-dir-up, .compass-dir-down {
            width: 38px;
            height: 38px;
            font-size: 0;
            color: #caa15c;
            border: 1px solid rgba(173,122,42,.45);
            text-shadow: none;
            background-repeat: no-repeat, no-repeat;
            background-position: center, center;
            background-size: 22px 22px, 100% 100%;
        }
        .compass-dir-up.compass-disabled, .compass-dir-down.compass-disabled {
            color: #b5aa92;
            border-color: rgba(173,122,42,.2);
            opacity: .45;
        }
        .compass-dir-up {
            top: -6px;
            right: -30px;
            background-image: url({{ asset('img/up.png') }}), radial-gradient(circle, rgba(74,46,18,.25), rgba(74,46,18,.08) 70%);
        }
        .compass-dir-down {
            bottom: -6px;
            right: -30px;
            background-image: url({{ asset('img/down.png') }}), radial-gradient(circle, rgba(74,46,18,.25), rgba(74,46,18,.08) 70%);
        }
        .compass-search {
            display: inline-block;
            align-self: flex-start;
            margin-top: 34px;
            padding: 6px 16px;
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(255,223,150,.18), rgba(74,46,18,.1));
            border: 1px solid rgba(173,122,42,.5);
            color: #4a2e12 !important;
            text-decoration: none !important;
            font-size: 12px;
            font-weight: 700;
            transition: background .15s ease, box-shadow .15s ease;
        }
        .compass-search:hover {
            background: linear-gradient(180deg, rgba(255,223,150,.4), rgba(74,46,18,.15));
            box-shadow: 0 0 10px 2px rgba(255,214,130,.5);
            text-decoration: none !important;
        }
    </style>
</head>
<body data-sound-context="location" data-sound-battle="{{ $page->hasBattle ? basename($page->battleUrl ?? '') : '' }}" data-sound-dungeon="{{ $page->dungeonSession?->sessionId ?? '' }}">
@php($moves = $page->moves)
<table class="main-table" width="100%" height="100%" style="height: 100%" cellpadding="10" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td style="vertical-align: top">
            <div class="center">
                <b><a href="" class="location-name">{{ $page->name }}</a></b> <span>({{ $page->locationId }})</span>
            </div>

            @if($resumableTrade !== null)
                <div id="location-trade-return" class="center" style="margin:8px 0;" data-expires-at="{{ $resumableTrade->expires_at->toIso8601String() }}">
                    <span class="butt1 pointer"><span>
                        <button class="butt1 shop" type="button" style="width:171px;" onclick="window.location.href = @js(route('player-trade.show', $resumableTrade->uuid))">{{ __('Вернуться к торговле') }}</button>
                    </span></span>
                </div>
                <script>
                    (() => {
                        const button = document.getElementById('location-trade-return');
                        const remaining = Date.parse(button.dataset.expiresAt) - Date.now();
                        setTimeout(() => button.remove(), Math.max(0, remaining));
                    })();
                </script>
            @endif

            <div style="overflow:hidden;">
                @if($page->image)
                    <table width="420" border="0" cellspacing="0" cellpadding="0" style="float:right; margin:0 0 8px 10px;">
                        <tbody>
                        <tr height="22">
                            <td width="20" align="right" valign="bottom"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-left.gif') }}" width="20" height="22" alt="" style="display:block;"></td>
                            <td class="location-frame-top" valign="top" align="center">&nbsp;</td>
                            <td width="20" align="left" valign="bottom"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-right.gif') }}" width="20" height="22" alt="" style="display:block;"></td>
                        </tr>
                        <tr>
                            <td class="location-frame-left">&nbsp;</td>
                            <td class="location-frame-bg" align="center" valign="middle" style="padding:4px;">
                                <img src="{{ $page->image }}" width="100%" alt="{{ $page->name }}" style="display:block;width:100%;height:auto;">
                            </td>
                            <td class="location-frame-right">&nbsp;</td>
                        </tr>
                        <tr height="18">
                            <td width="20" align="right" valign="top"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-left.gif') }}" width="20" height="18" alt="" style="display:block;"></td>
                            <td class="location-frame-bottom" valign="top" align="center">&nbsp;</td>
                            <td width="20" align="left" valign="top"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-right.gif') }}" width="20" height="18" alt="" style="display:block;"></td>
                        </tr>
                        </tbody>
                    </table>
                @endif

                <div class="location-description">{{ $page->description }}</div>

            @if($page->dungeonSession !== null)
                <div style="background:#ffe8b0; border:1px solid #c90; padding:4px 6px; margin-bottom:6px; font-size:11px;">
                    <b>{{ __("Данж:") }}</b> {{ $page->dungeonSession->name }}
                    @if($page->dungeonSession->isSurvival && $page->dungeonSession->waveCount)
                        &nbsp;|&nbsp;
                        @if($page->dungeonSession->allCleared)
                            <b style="color:#2a8a2a;">{{ __("Все волны пройдены! ✓") }}</b>
                        @else
                            {{ __("Волна") }} <b>{{ $page->dungeonSession->currentWave }}</b> / {{ $page->dungeonSession->waveCount }}
                        @endif
                    @endif
                    @if($page->dungeonSession->isTower && $page->dungeonSession->currentStageNumber)
                        &nbsp;|&nbsp; {{ __("Этаж") }} <b>{{ $page->dungeonSession->currentStageNumber }}</b>
                        @if($page->dungeonSession->currentStageName)
                            — {{ $page->dungeonSession->currentStageName }}
                        @endif
                    @endif
                    @if($page->dungeonSession->expiresAtTimestamp !== null)
                        — {{ __("осталось") }} <b id="dungeon-timer-loc"></b>
                        <script>
                            (function() {
                                const exp = {{ $page->dungeonSession->expiresAtTimestamp }};
                                function tick() {
                                    const left = exp - Math.floor(Date.now() / 1000);
                                    const el = document.getElementById('dungeon-timer-loc');
                                    if (!el) return;
                                    if (left <= 0) { el.textContent = '00:00'; window.location.reload(); return; }
                                    const m = String(Math.floor(left / 60)).padStart(2, '0');
                                    const s = String(left % 60).padStart(2, '0');
                                    el.textContent = m + ':' + s;
                                }
                                tick();
                                setInterval(tick, 1000);
                            })();
                        </script>
                    @endif
                    @if($page->dungeonSession->canExit)
                        &nbsp;
                        <form method="POST" action="{{ $page->dungeonSession->exitUrl }}" style="display:inline;">
                            @csrf
                            <button type="submit" style="background:#a33; color:#fff; border:none; padding:2px 8px; cursor:pointer; font-size:11px;">{{ __("Выйти из данжа") }}</button>
                        </form>
                    @endif
                </div>
            @endif

            @if($page->hasBattle)
                <div class="battle-description">
                    <p><span class="color-red"><b>{{ __("ВНИМАНИЕ!") }}</b></span> <b>{{ __("Вы атакованы!") }}</b></p>
                    <p><a href="{{ $page->battleUrl }}" id="attack">{{ __("Продолжить") }}</a> »</p>
                </div>
            @else
                @if($page->monsters !== [])
                    <div class="battle-description">
                        @foreach($page->monsters as $monster)
                            <div>
                                @if($monster->isBoss)
                                    <b>
                                        <a href="{{ $monster->infoUrl }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;" class="color-red">{{ $monster->name }}</a>
                                    </b>
                                @else
                                    <a href="{{ $monster->infoUrl }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;" class="color-red">{{ $monster->name }}</a>
                                @endif
                                [<a href="{{ $monster->attackUrl }}"
                                    @if($loop->first) id="weapon-attack" title="{{ __("Атаковать первого монстра (Q)") }}" @endif
                                    onclick="return parent.navigateGameAction?.(this.href) ?? true;">{{ __("атаковать") }}</a>]
                            </div>
                        @endforeach
                    </div>
                @endif

                @foreach($page->npcs as $npc)
                    <div>
                        @if($npc->infoUrl !== null)
                            <a href="{{ $npc->infoUrl }}" onclick="window.open(this.href,'','width=730,height=650,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;" class="color-red">{{ $npc->name }}</a>
                        @else
                            <span class="color-red">{{ $npc->name }}</span>
                        @endif
                        [<a href="{{ $npc->talkUrl }}">{{ __("говорить") }}</a>]
                    </div>
                @endforeach

                <div class="side-move">
                    <table cellspacing="0" cellpadding="0" align="left" border="0">
                        <tbody>
                        <tr>
                            <td width="215px" style="vertical-align: top">
                                <div class="compass-wrap">
                                    <div class="compass">
                                        @if($moves['north']->available)
                                            <a href="#" class="compass-dir compass-dir-north" onclick="actionGoTo(this, 'north')" id="{{ $moves['north']->elementId }}" target="game" title="{{ __($moves['north']->label) }}">{{ __($moves['north']->label) }}</a>
                                        @else
                                            <span class="compass-dir compass-dir-north compass-disabled">{{ __($moves['north']->label) }}</span>
                                        @endif

                                        @if($moves['south']->available)
                                            <a href="#" class="compass-dir compass-dir-south" onclick="actionGoTo(this, 'south')" id="{{ $moves['south']->elementId }}" target="game" title="{{ __($moves['south']->label) }}">{{ __($moves['south']->label) }}</a>
                                        @else
                                            <span class="compass-dir compass-dir-south compass-disabled">{{ __($moves['south']->label) }}</span>
                                        @endif

                                        @if($moves['west']->available)
                                            <a href="#" class="compass-dir compass-dir-west" onclick="actionGoTo(this, 'west')" id="{{ $moves['west']->elementId }}" target="game" title="{{ __($moves['west']->label) }}">{{ __($moves['west']->label) }}</a>
                                        @else
                                            <span class="compass-dir compass-dir-west compass-disabled">{{ __($moves['west']->label) }}</span>
                                        @endif

                                        @if($moves['east']->available)
                                            <a href="#" class="compass-dir compass-dir-east" onclick="actionGoTo(this, 'east')" id="{{ $moves['east']->elementId }}" target="game" title="{{ __($moves['east']->label) }}">{{ __($moves['east']->label) }}</a>
                                        @else
                                            <span class="compass-dir compass-dir-east compass-disabled">{{ __($moves['east']->label) }}</span>
                                        @endif

                                        @if($moves['up']->available)
                                            <a href="#" class="compass-dir compass-dir-up" onclick="actionGoTo(this, 'up')" id="move-up" target="game" title="{{ __($moves['up']->label) }}">▲</a>
                                        @else
                                            <span class="compass-dir compass-dir-up compass-disabled" title="{{ __($moves['up']->label) }}">▲</span>
                                        @endif
                                        @if($moves['down']->available)
                                            <a href="#" class="compass-dir compass-dir-down" onclick="actionGoTo(this, 'down')" id="move-down" target="game" title="{{ __($moves['down']->label) }}">▼</a>
                                        @else
                                            <span class="compass-dir compass-dir-down compass-disabled" title="{{ __($moves['down']->label) }}">▼</span>
                                        @endif
                                    </div>

                                    <a href="{{ $page->takeItemsUrl }}" id="take-item" target="game" class="compass-search">
                                        {{ __('Искать здесь') }} @if($page->itemsOnLocationCount)({{ $page->itemsOnLocationCount }}) @endif »
                                    </a>
                                </div>
                            </td>
                            <td style="vertical-align: top; padding-left: 15px">
                                @if($page->structures !== [] || $page->gateActions !== [])
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr height="22">
                                            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                            <td class="tbl-shp-sml tt" valign="top" align="center"></td>
                                            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                                        </tr>
                                        <tr>
                                            <td class="tbl-shp-sides ls">&nbsp;</td>
                                            <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 14px 0">
                                                @foreach($page->gateActions as $gateAction)
                                                    <div class="structures" style="margin: 5px">
                                                        <span class="butt1 pointer"><span><a href="{{ $gateAction->url }}" target="game" class="button">{{ $gateAction->label }}</a></span></span>
                                                    </div>
                                                @endforeach
                                                @foreach($page->structures as $structure)
                                                    @if(! $loop->first && count($page->structures) > 1)
                                                        <div class="rep-divider" aria-hidden="true"></div>
                                                    @endif
                                                    <div class="structures" style="margin: 5px">
                                                        <span class="butt1 pointer"><span><button class="butt1 shop" @if($structure->isHeal) data-heal-url="{{ $structure->entryUrl }}" @else data-href="{{ $structure->entryUrl }}" @endif type="submit">{{ $structure->name }}</button></span></span>
                                                        @if($structure->actions !== [])
                                                            <ul>
                                                                @foreach($structure->actions as $action)
                                                                    <li><a href="{{ $action->url }}" class="actions" @if($action->isHeal) data-heal-url="{{ $action->url }}" @endif><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->label }}</a></li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                @endforeach
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
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            @endif
            </div>
        </td>
    </tr>
    </tbody>
</table>

<script>
    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }

    const currentLocationId = {{ $page->locationId }};
    parent.sendToFrame('map-frame', { currentLocationId });
    window.top.postMessage({ type: 'playerLocationChanged', locationId: currentLocationId }, window.location.origin);

    (function () {
        const users = {!! $page->locationUsersJson !!};

        try {
            const chatFrame = window.parent.document.getElementById('chat-frame');
            if (!chatFrame) return;
            const whoFrame = chatFrame.contentDocument.getElementById('who-frame');
            if (!whoFrame) return;
            whoFrame.contentWindow.postMessage({ type: 'locationUsers', users: users }, '*');
        } catch (e) {}
    })();

    // Родитель читает это поле у текущего фрейма локации, когда отложенный
    // переход срывается с кулдауна: направления берутся уже с новой локации.
    window.availableMoves = @js($page->hasBattle
        ? []
        : array_values(array_keys(array_filter(
            $page->moves,
            static fn ($move): bool => $move->available,
        ))));

    function actionGoTo(button, direction) {
        // См. комментарий в battle::index — родитель может быть старее фрейма.
        if (typeof parent.queueMove === 'function') {
            parent.queueMove(direction);

            return;
        }

        parent.queueAction(() => {
            if (parent.goTo(direction) !== false) {
                parent.startCooldown();
            }
        });
    }

    document.querySelectorAll('.shop').forEach(function(button) {
        button.addEventListener('click', function(event) {
            const healUrl = this.getAttribute('data-heal-url');
            if (healUrl) {
                event.preventDefault();
                healAtFountain(healUrl, this);
                return;
            }

            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    document.querySelectorAll('[data-heal-url].actions').forEach(function(action) {
        action.addEventListener('click', function(event) {
            event.preventDefault();
            healAtFountain(this.getAttribute('data-heal-url'), this);
        });
    });

    async function healAtFountain(url, trigger) {
        if (trigger.dataset.healing === 'true') {
            return;
        }

        trigger.dataset.healing = 'true';
        trigger.setAttribute('aria-disabled', 'true');
        if (trigger instanceof HTMLButtonElement) {
            trigger.disabled = true;
        }

        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || gameTranslate("Не удалось восстановить здоровье."));
            }

            hp = data.hp;
            mp = data.mp;
            parent.sendToFrame('character-frame', { hp, mp, experience, lvl });
            parent.openGameMessageModal({ title: gameTranslate("Целительный фонтан"), message: data.message });
        } catch (error) {
            parent.showErrorIframe(error.message || gameTranslate("Не удалось восстановить здоровье."));
        } finally {
            delete trigger.dataset.healing;
            trigger.removeAttribute('aria-disabled');
            if (trigger instanceof HTMLButtonElement) {
                trigger.disabled = false;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.butt1.shop');
        const actions = document.querySelectorAll('.actions');
        let maxWidth = 0;

        buttons.forEach(function(button) {
            button.style.width = 'auto';
            maxWidth = Math.max(maxWidth, button.offsetWidth);
        });

        actions.forEach(function(action) {
            maxWidth = Math.max(maxWidth, action.offsetWidth);
        });

        buttons.forEach(function(button) {
            button.style.width = (maxWidth + 1) + 'px';
        });
    });

    let hp = { current: parseInt('{{ $page->playerFrame->hpCurrent }}'), max: parseInt('{{ $page->playerFrame->hpMax }}') };
    let mp = { current: parseInt('{{ $page->playerFrame->mpCurrent }}'), max: parseInt('{{ $page->playerFrame->mpMax }}') };
    let experience = parseFloat('{{ $page->playerFrame->experience }}');
    let lvl = parseInt('{{ $page->playerFrame->level }}');

    parent.sendToFrame('character-frame', { hp, mp, experience, lvl });

    @if(session('message'))
        try { window.parent.showErrorIframe('{{ session('message') }}'); } catch (e) {}
    @endif
    @if(session()->has('wallet_money') || session()->has('wallet_diamond'))
        parent.sendToFrame('character-frame', {
            @if(session()->has('wallet_money'))money: {{ (int) session('wallet_money') }},@endif
            @if(session()->has('wallet_diamond'))diamond: {{ (float) session('wallet_diamond') }},@endif
        });
    @endif
</script>
</body>
</html>
